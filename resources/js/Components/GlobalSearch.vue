<script setup>
import { ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const { saddle } = usePage().props;
const endpoint = `/${saddle.path}/resources/search`;

const term = ref('');
const groups = ref([]);
const open = ref(false);
let timer = null;

function onInput() {
    clearTimeout(timer);
    if (term.value.trim() === '') {
        groups.value = [];
        open.value = false;
        return;
    }
    timer = setTimeout(async () => {
        const res = await fetch(`${endpoint}?q=${encodeURIComponent(term.value)}`, {
            headers: { Accept: 'application/json' },
        });
        const data = await res.json();
        groups.value = data.groups;
        open.value = true;
    }, 250);
}

function go(url) {
    open.value = false;
    term.value = '';
    groups.value = [];
    router.visit(url);
}
</script>

<template>
    <div class="relative border-b border-line px-3 py-2">
        <input
            v-model="term"
            type="search"
            :placeholder="t('index.search')"
            class="w-full rounded-lg border border-line-2 bg-bg px-3 py-1.5 text-sm"
            @input="onInput"
            @focus="term && (open = true)"
            @keydown.escape="open = false"
        />
        <div
            v-if="open && groups.length"
            class="absolute left-3 right-3 z-20 mt-1 max-h-80 overflow-y-auto rounded-lg border border-line bg-bg shadow-lg"
        >
            <div v-for="group in groups" :key="group.uriKey" class="py-1">
                <p class="px-3 py-1 text-[0.65rem] font-medium uppercase tracking-wide text-ink-3">{{ group.label }}</p>
                <button
                    v-for="r in group.results"
                    :key="r.id"
                    type="button"
                    class="block w-full px-3 py-1.5 text-left text-sm hover:bg-surface-2"
                    @click="go(r.url)"
                >{{ r.title }}</button>
            </div>
        </div>
        <p
            v-else-if="open"
            class="absolute left-3 right-3 z-20 mt-1 rounded-lg border border-line bg-bg px-3 py-2 text-sm text-ink-3 shadow-lg"
        >No matches.</p>
    </div>
</template>
