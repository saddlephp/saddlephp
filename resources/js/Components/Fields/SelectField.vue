<script setup>
defineProps({ field: Object });
const model = defineModel();
</script>

<template>
    <select v-model="model" class="w-full max-w-lg rounded-lg border border-line-2 bg-bg px-3 py-2 text-sm">
        <option :value="null">&#x2014;</option>
        <option v-for="option in field.options" :key="option.value" :value="option.value">{{ option.label }}</option>
    </select>
</template>
