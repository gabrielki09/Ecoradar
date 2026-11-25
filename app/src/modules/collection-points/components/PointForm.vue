<template>
    <q-form class="grid grid-cols-1 gap-4 rounded-2xl border border-line bg-white p-6" @submit.prevent="submit">
        <p v-if="message" role="alert" class="text-negative">{{ message }}</p>
        <q-select
            v-if="auth.user?.profile_type === 'company'"
            :model-value="companyId"
            name="company_id"
            :disable="!!initial || sending"
            :options="companyOptions"
            label="Empresa responsável *"
            outlined
            emit-value
            map-options
            :error="!!errors.company_id"
            :error-message="errors.company_id"
            @update:model-value="store.selectCompany"
        />
        <q-input
            v-model="form.name"
            name="name"
            outlined
            label="Nome *"
            :error="!!errors.name"
            :error-message="errors.name"
        />
        <q-input
            v-model="form.cep"
            name="cep"
            outlined
            type="tel"
            inputmode="numeric"
            autocomplete="postal-code"
            label="CEP *"
            placeholder="00000-000"
            mask="#####-###"
            maxlength="9"
            :error="!!errors.cep"
            :error-message="errors.cep"
            @update:model-value="handleAutocompleteCEP(String($event ?? ''))"
        />
        <q-input v-for="field in fieldLabels.slice(1)" :key="field.key"
            v-model="form[field.key]"
            :name="field.key"
            outlined
            :type="field.key === 'guidelines' ? 'textarea' : 'text'"
            :label="field.label"
            :error="!!errors[field.key]"
            :error-message="errors[field.key]"
        />
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <q-input v-model.number="form.latitude" name="latitude" outlined type="number" step="any" label="Latitude (opcional)" hint="Coordenada exata do endereço" :error="!!errors.latitude" :error-message="errors.latitude" />
            <q-input v-model.number="form.longitude" name="longitude" outlined type="number" step="any" label="Longitude (opcional)" hint="Informe os dois valores para exibir no mapa" :error="!!errors.longitude" :error-message="errors.longitude" />
        </div>

        <HoursEditor v-model="hours" :errors="errors" />

        <section data-error-field="waste_categories" tabindex="-1">
            <h2 class="text-xl font-bold text-eco-900">Materiais recebidos</h2>
            <p v-if="catalogLoading" role="status">Carregando categorias…</p>
            <p v-if="catalogError" role="alert" class="text-negative">{{ catalogError }}</p>
            <q-btn v-if="catalogError" flat label="Tentar novamente" @click="loadCategories" />
            <p v-if="errors.waste_categories" role="alert" class="text-negative">{{ errors.waste_categories }}</p>
            <q-select
                v-model="categories"
                :options="categoryOptions"
                multiple
                use-chips
                emit-value
                map-options
                outlined
                label="Categorias *"
                :disable="catalogLoading || !!catalogError"
            />
            <p v-for="(value, key) in errors" :key="key" v-show="key.startsWith('waste_categories.')" role="alert" class="text-negative">
                {{ value }}
            </p>
        </section>

        <div class="flex flex-wrap gap-3">
            <q-btn
                type="submit"
                color="primary"
                :label="initial ? 'Salvar alterações' : 'Criar ponto'"
                :loading="sending"
                :disable="catalogLoading || !!catalogError"
            />
            <q-btn
                flat
                label="Cancelar"
                :to="initial ? { name: 'point-detail', params: { id: initial.id }, query: companyQuery } : { name: 'point-mine' }"
                :disable="sending"
            />
        </div>
    </q-form>
</template>

<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useQuasar } from 'quasar';
import { scrollToFirstError } from '@/helpers/pages/scrollToFirstError';
import { readFormError } from '@/modules/auth/utils/form-errors';
import { useCepAutocomplete } from '@/composables/useCepAutocomplete';
import { cepDigits, formatCep } from '@/utils/cep';
import { useAuthStore } from '@/modules/auth/stores/auth.store';
import HoursEditor from './HoursEditor.vue';
import { pointService } from '../service';
import { usePointStore } from '../store';
import type { CollectionPoint, OpeningHour, PointFields, UpdatePointPayload, WasteCategory } from '../types';

const props = defineProps<{ initial?: CollectionPoint }>();
const router = useRouter();
const $q = useQuasar();
const auth = useAuthStore();
const store = usePointStore();
const fieldLabels: { key: keyof PointFields; label: string }[] = [
    { key: 'name', label: 'Nome *' },
    { key: 'neighborhood', label: 'Bairro *' }, { key: 'address', label: 'Endereço *' },
    { key: 'guidelines', label: 'Orientações *' }, { key: 'contact', label: 'Contato *' },
];
const form = reactive<PointFields>({
    name: props.initial?.name ?? '', cep: formatCep(props.initial?.cep ?? ''),
    neighborhood: props.initial?.neighborhood ?? '', address: props.initial?.address ?? '',
    guidelines: props.initial?.guidelines ?? '', contact: props.initial?.contact ?? '',
    latitude: props.initial?.latitude ?? null, longitude: props.initial?.longitude ?? null,
});
const handleAutocompleteCEP = useCepAutocomplete(form);
const hours = ref<OpeningHour[]>(props.initial?.opening_hours?.map(hour => ({
    opening_day: Number(hour.opening_day), opens_at: hour.opens_at.slice(0, 5),
    closes_at: hour.closes_at.slice(0, 5),
})) ?? []);
const categories = ref<number[]>(props.initial?.collection_waste_category?.map(item => item.waste_category_id) ?? []);
const catalog = ref<WasteCategory[]>([]);
const catalogLoading = ref(false);
const catalogError = ref('');
const sending = ref(false);
const message = ref('');
const errors = reactive<Record<string, string>>({});
const companyId = computed(() => auth.user?.profile_type === 'company'
    ? props.initial?.responsible_operator_id ?? store.selectedCompanyId
    : null);
const companyQuery = computed(() => companyId.value ? { empresa: companyId.value } : {});
const companyOptions = computed(() => store.companies.map(company => ({ label: company.company_name, value: company.id })));
const categoryOptions = computed(() => catalog.value.map(category => ({ label: category.name, value: category.id })));

async function loadCategories() {
    catalogLoading.value = true;
    catalogError.value = '';
    try {
        catalog.value = await pointService.categories();
    } catch (cause) {
        catalogError.value = readFormError(cause, 'Não foi possível carregar as categorias.').message;
    } finally {
        catalogLoading.value = false;
    }
}

function validate() {
    Object.keys(errors).forEach(key => delete errors[key]);
    for (const field of fieldLabels) {
        if (!String(form[field.key] ?? '').trim()) errors[field.key] = 'Campo obrigatório.';
    }
    if (!/^\d{8}$/.test(cepDigits(form.cep))) errors.cep = 'Informe um CEP de 8 dígitos.';
    const latitude = coordinate(form.latitude);
    const longitude = coordinate(form.longitude);
    if ((latitude === null) !== (longitude === null)) errors.latitude = 'Informe latitude e longitude juntas.';
    if (latitude !== null && (!Number.isFinite(latitude) || Math.abs(latitude) > 90)) errors.latitude = 'Latitude inválida.';
    if (longitude !== null && (!Number.isFinite(longitude) || Math.abs(longitude) > 180)) errors.longitude = 'Longitude inválida.';
    if (auth.user?.profile_type === 'company' && !companyId.value) errors.company_id = 'Selecione uma empresa responsável.';
    if (!props.initial && !hours.value.length) errors.opening_hours = 'Selecione ao menos um dia.';
    if (!props.initial && !categories.value.length) errors.waste_categories = 'Selecione ao menos uma categoria.';
    const seen = new Set<number>();
    hours.value.forEach((hour, index) => {
        if (seen.has(hour.opening_day)) errors[`opening_hours.${index}.opening_day`] = 'Dia repetido.';
        seen.add(hour.opening_day);
        if (!/^\d{2}:\d{2}$/.test(hour.opens_at)) errors[`opening_hours.${index}.opens_at`] = 'Informe a abertura.';
        if (!/^\d{2}:\d{2}$/.test(hour.closes_at) || hour.closes_at <= hour.opens_at) {
            errors[`opening_hours.${index}.closes_at`] = 'O fechamento deve ser posterior à abertura.';
        }
    });
    if (new Set(categories.value).size !== categories.value.length) errors.waste_categories = 'Categoria repetida.';
    return !Object.keys(errors).length;
}

function coordinate(value: unknown): number | null {
    return value === '' || value === null || value === undefined ? null : Number(value);
}

async function submit() {
    if (sending.value || catalogLoading.value || catalogError.value) return;
    message.value = '';
    if (!validate()) {
        await scrollToFirstError(Object.keys(errors), errors);
        return;
    }
    sending.value = true;
    let submittedDays: number[] = [];
    try {
        const fields: Record<string, string | number | null> = Object.fromEntries(fieldLabels.map(({ key }) => [key, String(form[key] ?? '').trim()]));
        fields.cep = cepDigits(form.cep);
        fields.latitude = coordinate(form.latitude);
        fields.longitude = coordinate(form.longitude);
        const company = companyId.value ? { company_id: companyId.value } : {};
        let result: CollectionPoint;
        if (props.initial) {
            const changed = Object.fromEntries(Object.entries(fields).filter(([key, value]) => value !== props.initial?.[key as keyof PointFields]));
            const oldHours = new Map((props.initial.opening_hours ?? []).map(hour => [Number(hour.opening_day), hour]));
            const nextHours = new Map(hours.value.map(hour => [hour.opening_day, hour]));
            const hourChanges = [
                ...[...oldHours.keys()].filter(day => !nextHours.has(day)).map(day => ({ opening_day: day, delete: true as const })),
                ...hours.value.filter(hour => {
                    const old = oldHours.get(hour.opening_day);
                    return !old || old.opens_at.slice(0, 5) !== hour.opens_at || old.closes_at.slice(0, 5) !== hour.closes_at;
                }),
            ];
            submittedDays = hourChanges.map(hour => hour.opening_day);
            const oldCategories = new Set((props.initial.collection_waste_category ?? []).map(item => item.waste_category_id));
            const categoryChanges = [
                ...[...oldCategories].filter(id => !categories.value.includes(id)).map(waste_category_id => ({ waste_category_id, delete: true as const })),
                ...categories.value.filter(id => !oldCategories.has(id)).map(waste_category_id => ({ waste_category_id })),
            ];
            const payload: UpdatePointPayload = {
                ...changed, ...company,
                ...(hourChanges.length ? { opening_hours: hourChanges } : {}),
                ...(categoryChanges.length ? { waste_categories: categoryChanges } : {}),
            };
            result = await pointService.update(props.initial.id, payload);
        } else {
            submittedDays = hours.value.map(hour => hour.opening_day);
            result = await pointService.create({
                ...form, ...company,
                cep: String(fields.cep),
                latitude: coordinate(form.latitude),
                longitude: coordinate(form.longitude),
                opening_hours: hours.value,
                waste_categories: categories.value.map(waste_category_id => ({ waste_category_id })),
            });
        }
        store.invalidate();
        $q.notify({ type: 'positive', message: props.initial ? 'Ponto atualizado.' : 'Ponto criado.' });
        await router.replace({ name: 'point-detail', params: { id: result.id }, query: companyQuery.value });
    } catch (cause) {
        const parsed = readFormError(cause, 'Não foi possível salvar o ponto.');
        for (const [key, value] of Object.entries(parsed.fields)) {
            const match = /^opening_hours\.(\d+)\.(.+)$/.exec(key);
            const day = match ? submittedDays[Number(match[1])] : undefined;
            const formIndex = day === undefined ? -1 : hours.value.findIndex(hour => hour.opening_day === day);
            errors[formIndex >= 0 && match ? `opening_hours.${formIndex}.${match[2]}` : key] = value;
        }
        message.value = parsed.message;
        await scrollToFirstError(Object.keys(errors), errors);
    } finally {
        sending.value = false;
    }
}

onMounted(loadCategories);
</script>
