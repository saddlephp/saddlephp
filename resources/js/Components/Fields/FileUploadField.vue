<script setup>
import { computed, ref } from 'vue';

const props = defineProps({ field: Object });

// model holds the value submitted to the server: a File object (new upload),
// null (cleared), or — at init — null (the stored path is intentionally never
// the model value; the page strips an untouched null file key before submit).
const model = defineModel();

// Emitted whenever the user picks or clears, so the page can mark this file key
// as "touched" and therefore include it in the submission.
const emit = defineEmits(['touched']);

const input = ref(null);
const picked = ref(null);

// The stored path string lives on field.value (resolve() output), never in the
// form model. We show its basename so the user knows a file already exists.
const storedName = computed(() => {
    const path = props.field.value;
    if (typeof path !== 'string' || path === '') return null;
    const parts = path.split('/');
    return parts[parts.length - 1];
});

function onChange(event) {
    const file = event.target.files?.[0] ?? null;
    picked.value = file ? file.name : null;
    model.value = file;
    emit('touched');
}

function clear() {
    picked.value = null;
    model.value = null;
    if (input.value) input.value.value = '';
    emit('touched');
}
</script>

<template>
    <div class="w-full max-w-lg space-y-2">
        <div v-if="storedName && !picked" class="flex items-center justify-between rounded-lg border border-line-2 bg-surface px-3 py-2 text-sm">
            <span class="truncate text-ink-2">{{ storedName }}</span>
            <button type="button" class="ml-3 shrink-0 text-xs text-accent" @click="clear">Clear</button>
        </div>

        <input
            ref="input"
            type="file"
            :accept="field.accept ?? undefined"
            class="block w-full text-sm text-ink-2 file:mr-3 file:rounded-lg file:border-0 file:bg-surface-2 file:px-3 file:py-2 file:text-sm file:font-medium file:text-ink hover:file:bg-line"
            @change="onChange"
        />

        <p v-if="picked" class="text-xs text-ink-3">Selected {{ picked }}</p>
    </div>
</template>
