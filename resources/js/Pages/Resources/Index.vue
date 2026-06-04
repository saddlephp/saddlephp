<script setup>
import { ref, watch, onUnmounted } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import PanelLayout from '../../Components/PanelLayout.vue';
import ConfirmDialog from '../../Components/ConfirmDialog.vue';

const props = defineProps({
    resource: Object,
    columns: Array,
    rows: Object,
    query: Object,
});

const { saddle } = usePage().props;
const base = `/${saddle.path}/resources/${props.resource.uriKey}`;

const search = ref(props.query.search);
let timer;
watch(search, (value) => {
    clearTimeout(timer);
    timer = setTimeout(
        () => router.get(base, { ...props.query, search: value, page: 1 }, { preserveState: true, replace: true }),
        350,
    );
});
onUnmounted(() => clearTimeout(timer));

function sortBy(column) {
    if (!column.sortable) return;
    const direction = props.query.sort === column.name && props.query.direction === 'asc' ? 'desc' : 'asc';
    router.get(base, { ...props.query, sort: column.name, direction }, { preserveState: true, replace: true });
}

const deleting = ref(null);
function destroy() {
    router.delete(`${base}/${deleting.value.id}`, { onFinish: () => (deleting.value = null) });
}

const badgeStyles = {
    accent: 'bg-accent/10 text-accent',
    ink: 'bg-ink text-white',
    muted: 'bg-surface-2 text-ink-2',
};

function badgeClass(column, value) {
    return badgeStyles[column.colors?.[value]] ?? badgeStyles.muted;
}
</script>

<template>
    <PanelLayout>
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-2xl font-semibold tracking-tight">{{ resource.label }}</h1>
            <Link
                v-if="resource.canCreate"
                :href="`${base}/create`"
                class="rounded-lg bg-accent px-4 py-2 text-sm font-medium text-white"
            >New {{ resource.singularLabel.toLowerCase() }}</Link>
        </div>

        <input
            v-model="search"
            type="search"
            placeholder="Search&#x2026;"
            class="mt-5 w-full max-w-xs rounded-lg border border-line-2 bg-bg px-3 py-2 text-sm"
        />

        <div class="mt-4 overflow-hidden rounded-xl border border-line bg-bg">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-line bg-surface text-xs uppercase tracking-wide text-ink-3">
                    <tr>
                        <th v-for="column in columns" :key="column.name" class="px-4 py-3 font-medium">
                            <button
                                v-if="column.sortable"
                                type="button"
                                class="inline-flex items-center gap-1 uppercase"
                                @click="sortBy(column)"
                            >
                                {{ column.label }}
                                <span v-if="query.sort === column.name">{{ query.direction === 'asc' ? '↑' : '↓' }}</span>
                            </button>
                            <span v-else>{{ column.label }}</span>
                        </th>
                        <th class="w-28"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    <tr v-for="row in rows.data" :key="row.id" class="transition hover:bg-surface">
                        <td v-for="column in columns" :key="column.name" class="px-4 py-3">
                            <span
                                v-if="column.type === 'badge' && row.cells[column.name] != null"
                                :class="['inline-flex rounded-full px-2 py-0.5 text-[0.72rem] font-medium', badgeClass(column, row.cells[column.name])]"
                            >{{ row.cells[column.name] }}</span>
                            <svg
                                v-else-if="column.type === 'boolean' && row.cells[column.name]"
                                role="img" aria-label="Yes"
                                viewBox="0 0 24 24" class="h-4 w-4 text-accent" fill="none" stroke="currentColor" stroke-width="2.4"
                            ><path d="m20 6-11 11-5-5" /></svg>
                            <span v-else-if="column.type === 'boolean'" aria-label="No" class="text-ink-3">&mdash;</span>
                            <template v-else>{{ row.cells[column.name] }}</template>
                        </td>
                        <td class="px-4 py-3 text-right text-xs">
                            <Link v-if="row.can.update" :href="`${base}/${row.id}/edit`" class="text-ink-2 hover:text-ink">Edit</Link>
                            <button v-if="row.can.delete" type="button" class="ml-3 text-accent" @click="deleting = row">Delete</button>
                        </td>
                    </tr>
                    <tr v-if="!rows.data.length">
                        <td :colspan="columns.length + 1" class="px-4 py-10 text-center text-ink-3">Nothing in the corral yet.</td>
                    </tr>
                </tbody>
            </table>
            <div
                v-if="rows.last_page > 1"
                class="flex items-center justify-between border-t border-line bg-surface px-4 py-2.5 text-xs text-ink-3"
            >
                <span>{{ rows.from }}&#x2013;{{ rows.to }} of {{ rows.total }}</span>
                <div class="flex gap-1">
                    <Link
                        v-for="link in rows.links"
                        :key="link.label"
                        :href="link.url ?? '#'"
                        class="rounded border px-2 py-1"
                        :class="link.active ? 'border-ink bg-ink text-white' : 'border-line bg-bg'"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>

        <ConfirmDialog v-if="deleting" :title="`Delete ${deleting.title}?`" @confirm="destroy" @cancel="deleting = null" />
    </PanelLayout>
</template>
