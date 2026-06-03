<script setup>
defineProps({ field: Object });
const model = defineModel();
</script>

<template>
    <textarea
        v-model="model"
        :rows="field.rows ?? 4"
        :placeholder="field.placeholder ?? ''"
        class="w-full max-w-lg rounded-lg border border-line-2 bg-bg px-3 py-2 text-sm"
    ></textarea>
</template>
