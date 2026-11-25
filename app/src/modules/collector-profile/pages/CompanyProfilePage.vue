<template>
    <q-page class="bg-cream px-4 py-8">
        <main class="mx-auto max-w-[850px]">
            <RouterLink
                to="/area"
                class="text-eco-700"
            >
                ← Minha área
            </RouterLink>

            <h1 class="text-3xl font-extrabold text-eco-900">Perfil da empresa</h1>
            <p class="text-muted">Configure as cidades atendidas e os materiais aceitos para receber solicitações aplicáveis.</p>

            <q-select
                v-if="companyOptions.length > 1"
                :model-value="store.selectedCompanyId"
                :options="companyOptions"
                :disable="saving"
                label="Empresa"
                outlined
                emit-value
                map-options
                class="mb-4"
                @update:model-value="store.selectCompany"
            />

            <p v-if="loading" role="status">Carregando perfil…</p>
            <div v-else-if="loadError" role="alert" class="text-negative">
                {{ loadError }}
                <q-btn flat label="Tentar novamente" @click="load" />
            </div>

            <q-form
                v-else-if="form"
                class="grid min-w-0 gap-4 rounded-2xl border border-line bg-white p-5 sm:grid-cols-2"
                @submit="save"
            >
                <q-input
                    :model-value="form.document"
                    label="CNPJ"
                    outlined
                    readonly
                    class="sm:col-span-2"
                />
                <q-input
                    v-for="field in fields"
                    :key="field.key"
                    v-model="form[field.key]"
                    :name="field.key"
                    :label="field.label"
                    :error="Boolean(errors[field.key])"
                    :error-message="errors[field.key]"
                    :maxlength="field.max"
                    :disable="saving"
                    outlined
                />

                <CitySelect
                    :model-value="form.cities"
                    @update:model-value="form.cities = Array.isArray($event) ? $event : []"
                    name="cities"
                    multiple
                    label="Cidades atendidas"
                    :error="errors.cities ?? ''"
                    :disable="saving"
                    class="min-w-0 sm:col-span-2"
                />

                <q-select
                    v-model="form.waste_categories"
                    name="waste_categories"
                    :options="categoryOptions"
                    :error="Boolean(errors.waste_categories)"
                    :error-message="errors.waste_categories"
                    :disable="saving"
                    multiple
                    use-chips
                    emit-value
                    map-options
                    outlined
                    label="Categorias aceitas"
                    class="min-w-0 sm:col-span-2"
                />

                <p v-if="saveError" role="alert" class="text-negative sm:col-span-2">{{ saveError }}</p>
                <div class="flex flex-wrap gap-3 sm:col-span-2">
                    <q-btn
                        color="primary"
                        type="submit"
                        label="Salvar configuração"
                        :loading="saving"
                    />
                    <q-btn
                        flat
                        :disable="saving"
                        label="Cancelar"
                        to="/area"
                    />
                </div>
            </q-form>
        </main>
    </q-page>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { useQuasar } from 'quasar';

import { api } from '@/boot/axios';
import CitySelect from '@/components/CitySelect.vue';
import { scrollToFirstError } from '@/helpers/pages/scrollToFirstError';
import { useAuthStore } from '@/modules/auth/stores/auth.store';
import { readFormError } from '@/modules/auth/utils/form-errors';
import { usePointStore } from '@/modules/collection-points/store';
import type { ApiResponse } from '@/types/api/api.types';
import { cepDigits, formatCep } from '@/utils/cep';
import { collectorProfileService } from '../services/collector-profile.service';

const fields = [
    { key: 'company_name', label: 'Razão social', max: 255 },
    { key: 'responsible', label: 'Responsável', max: 120 },
    { key: 'contact_email', label: 'E-mail de contato', max: 255 },
    { key: 'cep', label: 'CEP', max: 9 },
    { key: 'neighborhood', label: 'Bairro', max: 255 },
    { key: 'address', label: 'Endereço', max: 255 },
] as const;

type CompanyForm = Record<(typeof fields)[number]['key'], string> & {
    document: string;
    cities: number[];
    waste_categories: number[];
};
type CompanyProfile = Omit<CompanyForm, 'cities' | 'waste_categories'> & {
    cities: { city_id: number }[];
    waste_categories: { waste_category_id: number }[];
};

const $q = useQuasar();
const auth = useAuthStore();
const store = usePointStore();
const form = ref<CompanyForm>();
const categoryOptions = ref<{ label: string; value: number }[]>([]);
const loading = ref(false);
const saving = ref(false);
const loadError = ref('');
const saveError = ref('');
const errors = ref<Record<string, string>>({});
const companyOptions = computed(() => store.companies.map(company => ({
    label: company.company_name,
    value: company.id,
})));
let sequence = 0;

async function load() {
    const current = ++sequence;
    form.value = undefined;
    loadError.value = '';
    saveError.value = '';
    errors.value = {};
    if (!store.selectedCompanyId) return;

    const companyId = store.selectedCompanyId;
    loading.value = true;
    try {
        const [response, catalog] = await Promise.all([
            api.get<ApiResponse<{ company: CompanyProfile }>>(`/company-profile/${companyId}`),
            collectorProfileService.getAllWasteCategories(),
        ]);
        if (current !== sequence) return;

        const company = response.data.data.company;
        form.value = {
            company_name: company.company_name,
            responsible: company.responsible,
            document: company.document,
            contact_email: company.contact_email ?? '',
            cep: formatCep(company.cep ?? ''),
            neighborhood: company.neighborhood ?? '',
            address: company.address ?? '',
            cities: company.cities.map(city => city.city_id),
            waste_categories: company.waste_categories.map(category => category.waste_category_id),
        };
        categoryOptions.value = catalog
            .filter(category => category.is_system || category.company_id === companyId)
            .map(category => ({ label: category.name, value: category.id }));
    } catch (cause) {
        if (current === sequence) {
            loadError.value = readFormError(cause, 'Não foi possível carregar o perfil da empresa.').message;
        }
    } finally {
        if (current === sequence) loading.value = false;
    }
}

async function save() {
    if (!form.value || !store.selectedCompanyId || saving.value) return;

    const { document, ...profile } = form.value;
    saving.value = true;
    errors.value = {};
    saveError.value = '';
    try {
        await api.put(`/company-profile/${store.selectedCompanyId}`, {
            ...profile,
            cep: profile.cep ? cepDigits(profile.cep) : null,
        });
        $q.notify({ type: 'positive', message: 'Perfil da empresa atualizado.' });
        await auth.refreshCurrentUser().catch(() => {
            // A configuração já foi salva. O rótulo da sessão será atualizado no próximo carregamento.
        });
    } catch (cause) {
        const parsed = readFormError(cause, 'Não foi possível salvar a configuração.');
        errors.value = Object.fromEntries(Object.entries(parsed.fields).map(([key, value]) => [key.split('.')[0], value]));
        saveError.value = parsed.message;
        await scrollToFirstError([...fields.map(field => field.key), 'cities', 'waste_categories'], errors.value);
    } finally {
        saving.value = false;
    }
}

watch(() => store.selectedCompanyId, load, { immediate: true });
</script>
