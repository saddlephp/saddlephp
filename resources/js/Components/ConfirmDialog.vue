<script setup>
defineProps({ title: String });
defineEmits(['confirm', 'cancel']);
</script>

<template>
    <div class="fixed inset-0 z-50 grid place-items-center bg-black/40 p-4">
        <div class="w-full max-w-sm rounded-xl border border-line bg-bg p-6">
            <p class="font-medium">{{ title }}</p>
            <p class="mt-1 text-sm text-ink-2">This can't be undone, partner.</p>
            <div class="mt-5 flex justify-end gap-3">
                <button type="button" class="rounded-lg border border-line-2 px-3 py-1.5 text-sm" @click="$emit('cancel')">Cancel</button>
                <button type="button" class="rounded-lg bg-accent px-3 py-1.5 text-sm font-medium text-white" @click="$emit('confirm')">Delete</button>
            </div>
        </div>
    </div>
</template>
