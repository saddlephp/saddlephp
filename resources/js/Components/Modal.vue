<script setup>
defineProps({ title: String });
const emit = defineEmits(['close']);
</script>

<template>
    <div class="fixed inset-0 z-40 flex justify-end bg-black/30" @click.self="emit('close')">
        <div class="h-full w-full max-w-md overflow-y-auto bg-bg p-6 shadow-xl">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold">{{ title }}</h2>
                <button type="button" class="text-2xl leading-none text-ink-3 hover:text-ink" @click="emit('close')">&times;</button>
            </div>
            <slot />
        </div>
    </div>
</template>
