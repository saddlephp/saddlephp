<script setup>
defineProps({ field: Object });
const model = defineModel();
</script>

<template>
    <input
        v-model="model"
        type="number"
        :min="field.min ?? undefined"
        :max="field.max ?? undefined"
        :step="field.step ?? undefined"
        :placeholder="field.placeholder ?? ''"
        class="w-full max-w-lg rounded-lg border border-line-2 bg-bg px-3 py-2 text-sm"
    />
</template>
