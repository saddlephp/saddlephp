<script setup>
import { reactive, ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import FormRenderer from './FormRenderer.vue';
import Modal from './Modal.vue';
import ConfirmDialog from './ConfirmDialog.vue';
import { flattenFields, isFileField } from '../support/flattenFields';

const props = defineProps({ relation: Object, base: String });
const endpoint = `${props.base}/relations/${props.relation.key}`;

const editing = ref(null); // null | 'create' | record id
const deleting = ref(null);

// The create schema is delivered with the page; the edit schema is fetched per
// record (so bound values and per-record field metadata are correct). Both
// share the same field NAMES, so one form object drives both.
const fields = ref(props.relation.createForm);
const leaves = () => flattenFields(fields.value);

const blank = Object.fromEntries(
    flattenFields(props.relation.createForm).map((f) => [f.name, isFileField(f) ? null : f.value]),
);
const form = useForm({ ...blank });

const touchedFiles = reactive(new Set());
form.__touchFile = (name) => touchedFiles.add(name);
form.transform((data) => {
    const out = { ...data };
    for (const f of leaves()) {
        if (isFileField(f) && out[f.name] === null && !touchedFiles.has(f.name)) delete out[f.name];
    }
    return out;
});

function load(schema) {
    fields.value = schema;
    touchedFiles.clear();
    for (const f of flattenFields(schema)) form[f.name] = isFileField(f) ? null : f.value;
    form.clearErrors();
}

function startCreate() {
    load(props.relation.createForm);
    editing.value = 'create';
}

async function startEdit(row) {
    const res = await fetch(`${endpoint}/${row.id}/edit`, { headers: { Accept: 'application/json' } });
    const data = await res.json();
    load(data.fields);
    editing.value = row.id;
}

function submit() {
    const opts = { preserveScroll: true, onSuccess: () => (editing.value = null) };
    editing.value === 'create'
        ? form.post(endpoint, opts)
        : form.put(`${endpoint}/${editing.value}`, opts);
}

function destroy() {
    router.delete(`${endpoint}/${deleting.value.id}`, {
        preserveScroll: true,
        onFinish: () => (deleting.value = null),
    });
}
</script>

<template>
    <section class="rounded-xl border border-line p-5">
        <div class="mb-3 flex items-center justify-between">
            <h3 class="text-sm font-semibold">{{ relation.label }}</h3>
            <button
                v-if="relation.canCreate"
                type="button"
                class="rounded-lg bg-accent px-3 py-1.5 text-xs font-medium text-white"
                @click="startCreate"
            >New</button>
        </div>

        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-line text-left text-xs text-ink-3">
                    <th v-for="col in relation.columns" :key="col.name" class="px-2 py-2">{{ col.label }}</th>
                    <th class="px-2 py-2"></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="row in relation.rows.data" :key="row.id" class="border-b border-line/60">
                    <td v-for="col in relation.columns" :key="col.name" class="px-2 py-2">{{ row.cells[col.name] }}</td>
                    <td class="px-2 py-2 text-right">
                        <button v-if="row.can.update" type="button" class="text-ink-2 hover:text-ink" @click="startEdit(row)">Edit</button>
                        <button v-if="row.can.delete" type="button" class="ml-3 text-accent" @click="deleting = row">Delete</button>
                    </td>
                </tr>
                <tr v-if="relation.rows.data.length === 0">
                    <td :colspan="relation.columns.length + 1" class="px-2 py-4 text-center text-ink-3">No records yet.</td>
                </tr>
            </tbody>
        </table>

        <Modal
            v-if="editing !== null"
            :title="editing === 'create' ? `New ${relation.label}` : `Edit ${relation.label}`"
            @close="editing = null"
        >
            <form @submit.prevent="submit">
                <FormRenderer :fields="fields" :form="form" />
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="mt-5 w-full rounded-lg bg-accent px-4 py-2 text-sm font-medium text-white disabled:opacity-60"
                >Save</button>
            </form>
        </Modal>

        <ConfirmDialog v-if="deleting" :title="`Delete ${deleting.title}?`" @confirm="destroy" @cancel="deleting = null" />
    </section>
</template>
