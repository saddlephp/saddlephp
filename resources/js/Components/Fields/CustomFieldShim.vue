<script setup>
const props = defineProps({ field: Object });
const model = defineModel();

function onInput(event) {
    model.value = event.detail;
}
</script>

<template>
    <component
        :is="field.tag"
        :name="field.name"
        :value.prop="model"
        :field.prop="props.field"
        class="block w-full max-w-lg"
        @saddle:input="onInput"
    />
</template>
