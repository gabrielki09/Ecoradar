<script setup lang="ts">
    const modelValue = defineModel<boolean>({ required: true });

    defineProps<{
        loading?: boolean
    }>();

    const emit = defineEmits<{
        save: [boolean]
    }>();

    const submit = () => {
        emit('save', true);
    };
</script>

<template>
    <q-dialog
        v-model="modelValue"
        persistent
    >
        <q-card class="w-full max-w-lg">
            <q-form
                @submit.prevent="submit"
                class="q-gutter-md"
            >
                <q-card-actions align="right">
                    <q-btn
                        flat
                        label="Cancelar"
                        :disable="loading"
                        @click="modelValue = false"
                    />

                    <q-btn
                        type="submit"
                        color="primary"
                        label="Confirmar"
                        :loading="loading"
                    />
                </q-card-actions>
            </q-form>
        </q-card>
    </q-dialog>
</template>
