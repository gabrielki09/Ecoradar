<template>
    <div>
        <label :for="id" class="mb-2 block text-sm font-bold text-eco-900">{{ label }}</label>
        <q-input :id="id" :model-value="modelValue" :name="name" :type="resolvedType" :autocomplete="autocomplete"
            :inputmode="inputmode" :placeholder="placeholder" :rules="rules" :error="error || undefined"
            :error-message="errorMessage" :hint="hint || undefined" outlined lazy-rules no-error-icon bg-color="white"
            color="green-8" class="auth-input" @update:model-value="$emit('update:modelValue', String($event ?? ''))"
            :maxlength="maxlength"
            :mask="mask"
            >
            <template v-if="icon" #prepend>
                <q-icon :name="icon" size="20px" aria-hidden="true" />
            </template>
            <template v-if="type === 'password'" #append>
                <q-btn flat round dense type="button" :icon="passwordVisible ? 'visibility_off' : 'visibility'"
                    :aria-label="passwordVisible ? 'Ocultar senha' : 'Mostrar senha'" :aria-pressed="passwordVisible"
                    @click="passwordVisible = !passwordVisible" />
            </template>
        </q-input>
    </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';

type FieldRule = (value: string) => true | string;

const props = withDefaults(defineProps<{
    id: string;
    name: string;
    label: string;
    modelValue: string;
    maxlength?: number;
    mask?: string;
    type?: 'text' | 'email' | 'password' | 'tel';
    autocomplete?: string;
    inputmode?: 'text' | 'email' | 'tel' | 'numeric';
    placeholder?: string;
    icon?: string;
    hint?: string;
    rules?: FieldRule[];
    error?: boolean;
    errorMessage?: string;
}>(), {
    type: 'text',
    autocomplete: 'off',
    inputmode: 'text',
    placeholder: '',
    icon: '',
    hint: '',
    rules: () => [],
    error: false,
    errorMessage: 'Verifique este campo.',
});

defineEmits<{ 'update:modelValue': [value: string] }>();

const passwordVisible = ref(false);
const resolvedType = computed(() => props.type === 'password' && passwordVisible.value ? 'text' : props.type);
</script>

<style scoped lang="scss">
.auth-input {
    :deep(.q-field__control) {
        min-height: 52px;
        border-radius: 12px;
        color: var(--eco-600);
    }

    :deep(.q-field__native) {
        color: var(--ink);
        font-size: 15px;
    }

    :deep(.q-field__prepend),
    :deep(.q-field__append) {
        color: #7a8d85;
    }

    :deep(.q-field__bottom) {
        padding: 6px 4px 0;
        color: var(--muted);
        font-size: 12px;
    }

    &.q-field--error :deep(.q-field__bottom) {
        color: #a83232;
    }
}
</style>
