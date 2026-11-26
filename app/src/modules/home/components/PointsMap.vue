<template>
    <div class="min-w-0">
        <div
            ref="container"
            class="points-map"
            role="application"
            aria-label="Mapa dos pontos de coleta"
        />
        <div
            v-if="tileFailed"
            class="points-state mt-3"
            role="status"
        >
            O mapa-base não carregou. Consulte os endereços na lista abaixo.
            <q-btn
                flat
                no-caps
                label="Tentar carregar o mapa"
                @click="retryTiles"
            />
        </div>
    </div>
</template>

<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import L from 'leaflet';
import 'leaflet.markercluster';
import 'leaflet/dist/leaflet.css';
import 'leaflet.markercluster/dist/MarkerCluster.css';
import 'leaflet.markercluster/dist/MarkerCluster.Default.css';
import type { PublicPoint } from '../types/public-point.types';

const props = defineProps<{ points: PublicPoint[]; selectedId: number | null }>();
const emit = defineEmits<{ select: [id: number] }>();
const container = ref<HTMLElement | null>(null);
const tileFailed = ref(false);
let map: L.Map | undefined;
let tiles: L.TileLayer | undefined;
let tileLoaded = false;
let cluster: L.MarkerClusterGroup | undefined;
let markerLayer: L.LayerGroup | undefined;
let frame = 0;
const markers = new Map<number, L.Marker>();
const pointIcon = L.divIcon({ className: 'eco-map-marker', html: '<span aria-hidden="true"></span>', iconSize: [44, 44], iconAnchor: [22, 44] });

function renderMarkers() {
    if (!map || !markerLayer) return;
    markerLayer.clearLayers();
    markers.clear();
    const bounds: L.LatLngExpression[] = [];
    for (const point of props.points) {
        const { latitude, longitude } = point;
        if (typeof latitude !== 'number' || typeof longitude !== 'number' ||
            !Number.isFinite(latitude) || !Number.isFinite(longitude) ||
            Math.abs(latitude) > 90 || Math.abs(longitude) > 180) continue;
        const location: L.LatLngExpression = [latitude, longitude];
        const marker = L.marker(location, { title: point.name, alt: point.name, icon: pointIcon, keyboard: true });
        marker.on('click', () => emit('select', point.id));
        markerLayer.addLayer(marker);
        markers.set(point.id, marker);
        bounds.push(location);
    }
    if (bounds.length) map.fitBounds(L.latLngBounds(bounds), { padding: [30, 30], maxZoom: 15 });
}

watch(() => props.points, renderMarkers);
watch(() => props.selectedId, id => {
    if (!map || id === null) return;
    const marker = markers.get(id);
    if (marker && cluster) cluster.zoomToShowLayer(marker, () => map?.panTo(marker.getLatLng()));
    else if (marker) map.panTo(marker.getLatLng());
});

function retryTiles() {
    tileFailed.value = false;
    tiles?.redraw();
}

onMounted(() => {
    if (!container.value) return;
    const first = props.points[0];
    if (!first || first.latitude === null || first.longitude === null) return;
    map = L.map(container.value, { scrollWheelZoom: false, tapHold: true })
        .setView([first.latitude, first.longitude], 13);
    tiles = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19,
    });
    tiles.on('tileload', () => {
        tileLoaded = true;
        tileFailed.value = false;
    });
    tiles.on('tileerror', () => {
        if (!tileLoaded) tileFailed.value = true;
    });
    tiles.addTo(map);
    if (typeof L.markerClusterGroup === 'function') {
        cluster = L.markerClusterGroup({ showCoverageOnHover: false, maxClusterRadius: 50 });
        markerLayer = cluster;
    } else {
        markerLayer = L.layerGroup();
    }
    map.addLayer(markerLayer);
    frame = requestAnimationFrame(() => {
        map?.invalidateSize();
        renderMarkers();
    });
});
onBeforeUnmount(() => { cancelAnimationFrame(frame); map?.remove(); map = undefined; });
</script>
