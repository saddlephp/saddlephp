<script setup>
import { ref, computed, watch, onUnmounted } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import PanelLayout from '../../Components/PanelLayout.vue';
import ConfirmDialog from '../../Components/ConfirmDialog.vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
    resource: Object,
    columns: Array,
    rows: Object,
    query: Object,
    filters: Array,
    actions: { type: Array, default: () => [] },
    bulkActions: { type: Array, default: () => [] },
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

function setFilter(name, value) {
    const filter = { ...props.query.filter };
    if (value === '') delete filter[name];
    else filter[name] = value;
    router.get(base, { ...props.query, filter, page: 1 }, { preserveState: true, replace: true });
}

const deleting = ref(null);
function destroy() {
    router.delete(`${base}/${deleting.value.id}`, { onFinish: () => (deleting.value = null) });
}

const forceDeleting = ref(null);
function restore(row) {
    router.put(`${base}/${row.id}/restore`, {}, { preserveScroll: true });
}
function forceDelete() {
    router.delete(`${base}/${forceDeleting.value.id}/force`, {
        preserveScroll: true,
        onFinish: () => (forceDeleting.value = null),
    });
}

function paginatorLabel(raw) {
    return raw.replace(/&laquo;\s*/g, '‹ ').replace(/\s*&raquo;/g, ' ›');
}

const badgeStyles = {
    accent: 'bg-accent/10 text-accent',
    ink: 'bg-ink text-white',
    muted: 'bg-surface-2 text-ink-2',
};

function badgeClass(column, value) {
    return badgeStyles[column.colors?.[value]] ?? badgeStyles.muted;
}

const actionTextStyles = {
    accent: 'text-accent',
    ink: 'text-ink-2 hover:text-ink',
    muted: 'text-ink-3',
};

function actionClass(action) {
    return actionTextStyles[action.color] ?? actionTextStyles.ink;
}

// Selection of the current page rows for bulk actions, keyed by row id.
const selected = ref([]);
const allOnPageSelected = computed(
    () => props.rows.data.length > 0 && selected.value.length === props.rows.data.length,
);

function toggleRow(id) {
    const next = new Set(selected.value);
    next.has(id) ? next.delete(id) : next.add(id);
    selected.value = [...next];
}

function toggleAllOnPage() {
    selected.value = allOnPageSelected.value ? [] : props.rows.data.map((row) => row.id);
}

function clearSelection() {
    selected.value = [];
}

// Shared confirm flow for row and bulk actions. When `confirming` is set the
// dialog is shown; its confirm event invokes the stored run callback.
const confirming = ref(null);

function runRowAction(action, row) {
    const post = () => router.post(
        `${base}/actions/${action.name}`,
        { record: row.id },
        { preserveScroll: true },
    );

    if (action.confirm) {
        confirming.value = { title: action.confirm, run: post };
        return;
    }

    post();
}

function runBulkAction(action) {
    const post = () => router.post(
        `${base}/actions/${action.name}`,
        { records: selected.value },
        { preserveScroll: true, onSuccess: clearSelection },
    );

    if (action.confirm) {
        confirming.value = { title: action.confirm, run: post };
        return;
    }

    post();
}

function confirmAction() {
    const run = confirming.value?.run;
    confirming.value = null;
    run?.();
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
            >{{ t('actions.create', { resource: resource.singularLabel.toLowerCase() }) }}</Link>
        </div>

        <input
            v-model="search"
            type="search"
            placeholder="Search&#x2026;"
            class="mt-5 w-full max-w-xs rounded-lg border border-line-2 bg-bg px-3 py-2 text-sm"
        />

        <div v-if="filters.length" class="mt-3 flex flex-wrap items-center gap-3">
            <label v-for="filter in filters" :key="filter.name" class="flex items-center gap-2 text-xs text-ink-2">
                {{ filter.label }}
                <select
                    :value="query.filter?.[filter.name] ?? ''"
                    class="rounded-lg border border-line-2 bg-bg px-2 py-1.5 text-sm text-ink"
                    @change="setFilter(filter.name, $event.target.value)"
                >
                    <option value="">All</option>
                    <template v-if="filter.type === 'boolean'">
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </template>
                    <template v-else>
                        <option v-for="option in filter.options" :key="option.value" :value="option.value">{{ option.label }}</option>
                    </template>
                </select>
            </label>
        </div>

        <div
            v-if="bulkActions.length && selected.length"
            class="mt-3 flex flex-wrap items-center gap-3 rounded-lg border border-line bg-surface px-4 py-2.5 text-sm"
        >
            <span class="text-ink-2">{{ selected.length }} selected</span>
            <button
                v-for="action in bulkActions"
                :key="action.name"
                type="button"
                :class="['font-medium', actionClass(action)]"
                @click="runBulkAction(action)"
            >{{ action.label }}</button>
            <button type="button" class="text-ink-3 hover:text-ink-2" @click="clearSelection">Clear</button>
        </div>

        <div class="mt-4 overflow-hidden rounded-xl border border-line bg-bg">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-line bg-surface text-xs uppercase tracking-wide text-ink-3">
                    <tr>
                        <th v-if="bulkActions.length" class="w-10 px-4 py-3">
                            <input
                                type="checkbox"
                                aria-label="Select all"
                                :checked="allOnPageSelected"
                                @change="toggleAllOnPage"
                            />
                        </th>
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
                        <td v-if="bulkActions.length" class="w-10 px-4 py-3">
                            <input
                                type="checkbox"
                                :aria-label="`Select ${row.title}`"
                                :checked="selected.includes(row.id)"
                                @change="toggleRow(row.id)"
                            />
                        </td>
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
                            <component
                                v-else-if="column.type === 'custom'"
                                :is="column.tag"
                                :value.prop="row.cells[column.name]"
                                :column.prop="column"
                            />
                            <template v-else>{{ row.cells[column.name] }}</template>
                        </td>
                        <td class="px-4 py-3 text-right text-xs">
                            <template v-if="!row.trashed">
                                <Link v-if="row.can.view" :href="`${base}/${row.id}`" class="text-ink-2 hover:text-ink">{{ t('rows.view') }}</Link>
                                <Link v-if="row.can.update" :href="`${base}/${row.id}/edit`" class="ml-3 text-ink-2 hover:text-ink">{{ t('rows.edit') }}</Link>
                                <button v-if="row.can.delete" type="button" class="ml-3 text-accent" @click="deleting = row">{{ t('rows.delete') }}</button>
                                <button
                                    v-for="action in actions"
                                    :key="action.name"
                                    type="button"
                                    :class="['ml-3', actionClass(action)]"
                                    @click="runRowAction(action, row)"
                                >{{ action.label }}</button>
                            </template>
                            <template v-else>
                                <button v-if="row.can.restore" type="button" class="text-ink-2 hover:text-ink" @click="restore(row)">{{ t('rows.restore') }}</button>
                                <button v-if="row.can.forceDelete" type="button" class="ml-3 text-accent" @click="forceDeleting = row">{{ t('rows.force_delete') }}</button>
                            </template>
                        </td>
                    </tr>
                    <tr v-if="!rows.data.length">
                        <td :colspan="columns.length + (bulkActions.length ? 2 : 1)" class="px-4 py-10 text-center text-ink-3">{{ t('index.empty') }}</td>
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
                    >{{ paginatorLabel(link.label) }}</Link>
                </div>
            </div>
        </div>

        <ConfirmDialog v-if="deleting" :title="t('confirm.delete', { title: deleting.title })" @confirm="destroy" @cancel="deleting = null" />
        <ConfirmDialog
            v-if="forceDeleting"
            :title="t('confirm.force_delete', { title: forceDeleting.title })"
            message="This cannot be undone."
            confirm-label="Delete permanently"
            @confirm="forceDelete"
            @cancel="forceDeleting = null"
        />
        <ConfirmDialog
            v-if="confirming"
            :title="confirming.title"
            message="Confirm this action, partner."
            confirm-label="Confirm"
            @confirm="confirmAction"
            @cancel="confirming = null"
        />
    </PanelLayout>
</template>
