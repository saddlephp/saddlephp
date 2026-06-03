<script setup>
defineProps({ field: Object });
const model = defineModel();
</script>

<template>
    <button
        type="button"
        role="switch"
        :aria-checked="!!model"
        class="relative h-6 w-11 rounded-full transition"
        :class="model ? 'bg-accent' : 'bg-line-2'"
        @click="model = !model"
    >
        <span
            class="absolute top-0.5 h-5 w-5 rounded-full bg-white transition-all"
            :class="model ? 'left-[22px]' : 'left-0.5'"
        ></span>
    </button>
</template>
