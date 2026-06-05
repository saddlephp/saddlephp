<script setup>
import TextField from './Fields/TextField.vue';
import TextareaField from './Fields/TextareaField.vue';
import SelectField from './Fields/SelectField.vue';
import ToggleField from './Fields/ToggleField.vue';
import NumberField from './Fields/NumberField.vue';
import DateField from './Fields/DateField.vue';
import DateTimeField from './Fields/DateTimeField.vue';
import MarkdownField from './Fields/MarkdownField.vue';
import FileUploadField from './Fields/FileUploadField.vue';
import SearchSelectField from './Fields/SearchSelectField.vue';
import CustomFieldShim from './Fields/CustomFieldShim.vue';
import SectionLayout from './Layout/SectionLayout.vue';
import GridLayout from './Layout/GridLayout.vue';
import TabsLayout from './Layout/TabsLayout.vue';

defineProps({ fields: Array, form: Object });

const map = {
    'text-field': TextField,
    'textarea-field': TextareaField,
    'select-field': SelectField,
    'toggle-field': ToggleField,
    'number-field': NumberField,
    'date-field': DateField,
    'datetime-field': DateTimeField,
    'markdown-field': MarkdownField,
    'file-field': FileUploadField,
    'search-select-field': SearchSelectField,
    'custom-field': CustomFieldShim,
};

const layouts = {
    section: SectionLayout,
    grid: GridLayout,
    tabs: TabsLayout,
};
</script>

<template>
    <div class="space-y-5">
        <template v-for="(node, index) in fields" :key="node.name ?? `node-${index}`">
            <component
                v-if="node.layout"
                :is="layouts[node.layout]"
                :node="node"
                :form="form"
            />
            <div v-else>
                <label class="mb-1.5 block text-sm font-medium">
                    {{ node.label }} <span v-if="node.required" class="text-accent">*</span>
                </label>
                <component
                    :is="map[node.component]"
                    :field="node"
                    v-model="form[node.name]"
                    @touched="node.component === 'file-field' ? form.__touchFile?.(node.name) : null"
                />
                <p v-if="node.helper" class="mt-1 text-xs text-ink-3">{{ node.helper }}</p>
                <p v-if="form.errors[node.name]" class="mt-1 text-xs text-accent">{{ form.errors[node.name] }}</p>
            </div>
        </template>
    </div>
</template>
