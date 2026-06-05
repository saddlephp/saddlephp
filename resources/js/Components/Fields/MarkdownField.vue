<script setup>
import { nextTick, ref } from 'vue';

defineProps({ field: Object });
const model = defineModel();

const textarea = ref(null);

/**
 * Insert markdown syntax around (or at) the current textarea selection. `wrap`
 * surrounds the selection; `prefix` is a line marker placed before it. When no
 * text is selected, a sensible placeholder is dropped in and re-selected so the
 * user can type over it.
 */
async function apply({ wrap, prefix, placeholder }) {
    const el = textarea.value;
    if (!el) return;

    const value = model.value ?? '';
    const start = el.selectionStart;
    const end = el.selectionEnd;
    const selected = value.slice(start, end) || placeholder || '';

    let inserted;
    let cursorStart;
    let cursorEnd;

    if (wrap) {
        inserted = `${wrap}${selected}${wrap}`;
        cursorStart = start + wrap.length;
        cursorEnd = cursorStart + selected.length;
    } else {
        inserted = `${prefix}${selected}`;
        cursorStart = start + prefix.length;
        cursorEnd = cursorStart + selected.length;
    }

    model.value = value.slice(0, start) + inserted + value.slice(end);

    await nextTick();
    el.focus();
    el.setSelectionRange(cursorStart, cursorEnd);
}

function link() {
    const el = textarea.value;
    if (!el) return;

    const value = model.value ?? '';
    const start = el.selectionStart;
    const end = el.selectionEnd;
    const text = value.slice(start, end) || 'text';
    const inserted = `[${text}](url)`;

    model.value = value.slice(0, start) + inserted + value.slice(end);

    nextTick(() => {
        el.focus();
        // Select the "url" placeholder so the user can replace it immediately.
        const urlStart = start + inserted.length - 4;
        el.setSelectionRange(urlStart, urlStart + 3);
    });
}

const buttons = [
    { key: 'bold', label: 'B', aria: 'Bold', run: () => apply({ wrap: '**', placeholder: 'bold text' }), classes: 'font-bold' },
    { key: 'italic', label: 'I', aria: 'Italic', run: () => apply({ wrap: '_', placeholder: 'italic text' }), classes: 'italic' },
    { key: 'link', label: '\u{1F517}', aria: 'Link', run: link, classes: '' },
    { key: 'list', label: '•', aria: 'Bulleted list', run: () => apply({ prefix: '- ', placeholder: 'list item' }), classes: '' },
];
</script>

<template>
    <div class="w-full max-w-lg">
        <div class="mb-1.5 flex gap-1">
            <button
                v-for="button in buttons"
                :key="button.key"
                type="button"
                :aria-label="button.aria"
                :class="['flex h-7 w-7 items-center justify-center rounded-md border border-line-2 bg-surface text-xs text-ink-2 transition hover:bg-surface-2 hover:text-ink', button.classes]"
                @click="button.run"
            >{{ button.label }}</button>
        </div>
        <textarea
            ref="textarea"
            v-model="model"
            :rows="field.rows ?? 8"
            :placeholder="field.placeholder ?? ''"
            class="w-full rounded-lg border border-line-2 bg-bg px-3 py-2 font-mono text-sm leading-relaxed"
        ></textarea>
    </div>
</template>
