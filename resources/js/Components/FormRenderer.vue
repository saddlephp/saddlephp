<script setup>
import TextField from './Fields/TextField.vue';
import TextareaField from './Fields/TextareaField.vue';
import SelectField from './Fields/SelectField.vue';
import ToggleField from './Fields/ToggleField.vue';
import NumberField from './Fields/NumberField.vue';
import DateField from './Fields/DateField.vue';
import SearchSelectField from './Fields/SearchSelectField.vue';
import CustomFieldShim from './Fields/CustomFieldShim.vue';

defineProps({ fields: Array, form: Object });

const map = {
    'text-field': TextField,
    'textarea-field': TextareaField,
    'select-field': SelectField,
    'toggle-field': ToggleField,
    'number-field': NumberField,
    'date-field': DateField,
    'search-select-field': SearchSelectField,
    'custom-field': CustomFieldShim,
};
</script>

<template>
    <div class="space-y-5">
        <div v-for="field in fields" :key="field.name">
            <label class="mb-1.5 block text-sm font-medium">
                {{ field.label }} <span v-if="field.required" class="text-accent">*</span>
            </label>
            <component :is="map[field.component]" :field="field" v-model="form[field.name]" />
            <p v-if="field.helper" class="mt-1 text-xs text-ink-3">{{ field.helper }}</p>
            <p v-if="form.errors[field.name]" class="mt-1 text-xs text-accent">{{ form.errors[field.name] }}</p>
        </div>
    </div>
</template>
