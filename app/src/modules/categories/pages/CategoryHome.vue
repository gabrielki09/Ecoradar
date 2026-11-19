<template>
    <q-page padding>
        <div class="p-4 block">
            <div>
                <q-table
                    title="Categorias"
                    :columns="columns"
                    :rows="categories"
                    row-key="id"
                    hide-no-data
                    :rows-per-page-label="'Registros por página'"
                >
                    <template v-slot:top-right>
                        <q-btn
                            color="primary"
                            label="Cadastrar"
                            @click="showFormDialog = true"
                            no-caps
                        />
                    </template>

                    <template v-slot:body="props">
                        <q-tr :props="props">
                            <q-td v-for="col in props.cols">
                                <div class="flex justify-center">
                                    <template v-if="col.name === 'actions'">
                                        <div class="flex gap-3">
                                            <q-btn
                                                color="primary"
                                                dense
                                                no-caps
                                                icon="edit"
                                            />

                                            <q-btn
                                                color="red"
                                                dense
                                                no-caps
                                                icon="delete"
                                                @click="
                                                    selectedCategoryId = props.row.id;
                                                    showConfirmDialog = true;
                                                "
                                            />
                                        </div>
                                    </template>

                                    <template v-else>
                                        {{ col.value }}
                                    </template>
                                </div>
                            </q-td>
                        </q-tr>
                    </template>
                </q-table>

                <CategoryFormDialog
                    v-model="showFormDialog"
                    :loading="loading"
                    @save="createCategory"
                />

                <ConfirmDeleteDialog
                    v-model="showConfirmDialog"
                    :loading="loading"
                    @save="deleteCategory"
                />
            </div>
        </div>
    </q-page>
</template>

<script setup lang="ts">
import { onMounted, ref } from "vue";
import type { CategoryPayload } from "../types/category.types";
import type { QTableColumn } from "quasar";
import { storeToRefs } from "pinia";
import { useCategoryStore } from "../stores/category.store";
import CategoryFormDialog from "../components/CategoryFormDialog.vue";
import { handleApiError } from "@/utils/api-error-handler";
import ConfirmDeleteDialog from "@/components/QDialog/ConfirmDeleteDialog.vue";

const categoryStore = useCategoryStore();
const { categories, loading } = storeToRefs(categoryStore);

const showFormDialog = ref<boolean>(false);
const showConfirmDialog = ref<boolean>(false);
const selectedCategoryId = ref<number | null>(null);

const columns: QTableColumn[] = [
    {
        label: "ID",
        name: "id",
        field: "id",
        align: "center",
    },
    {
        label: "Nome",
        name: "name",
        field: "name",
        align: "center",
    },
    {
        label: "Ações",
        name: "actions",
        field: "actions",
        align: "center",
    },
];

onMounted(async () => {
    void categoryStore.fetchCategories();
});

async function createCategory(payload: CategoryPayload) {
    try {
        await categoryStore.createCategory(payload);
        showFormDialog.value = false;
    } catch (error) {
        handleApiError(error, "Não foi possível cadastrar a categoria.");
    }
}

async function deleteCategory() {
    if (selectedCategoryId.value === null) {
        return;
    }
    try {
        await categoryStore.deleteCategory(selectedCategoryId.value);
        showConfirmDialog.value = false;
        selectedCategoryId.value = null;
    } catch (error) {
        handleApiError(error, "Não foi possível deletar a categoria.");
    }
}
</script>
