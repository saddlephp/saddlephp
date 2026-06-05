<script setup>
import { provide, reactive } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import PanelLayout from '../../Components/PanelLayout.vue';
import FormRenderer from '../../Components/FormRenderer.vue';
import { flattenFields, isFileField } from '../../support/flattenFields';

const props = defineProps({ resource: Object, record: Object, fields: Array });

const { saddle } = usePage().props;
const base = `/${saddle.path}/resources/${props.resource.uriKey}`;

provide('saddleOptionsBase', `${base}/options`);

const leaves = flattenFields(props.fields);
const fileNames = leaves.filter(isFileField).map((field) => field.name);

// File keys initialize to null, never the stored path (that string would fail
// the `file` rule). The stored path stays on field.value for display only; the
// transform below omits an untouched file key so editing without re-uploading
// leaves the existing file in place. Picking a file sets the File object;
// clicking Clear sets null AND marks the field touched, so null is submitted.
const form = useForm(
    Object.fromEntries(leaves.map((field) => [field.name, isFileField(field) ? null : field.value])),
);

const touchedFiles = reactive(new Set());
form.__touchFile = (name) => touchedFiles.add(name);

form.transform((data) => {
    const out = { ...data };
    for (const name of fileNames) {
        if (out[name] === null && !touchedFiles.has(name)) delete out[name];
    }
    return out;
});

function save() {
    form.put(`${base}/${props.record.id}`);
}
</script>

<template>
    <PanelLayout>
        <h1 class="text-2xl font-semibold tracking-tight">Edit {{ record.title }}</h1>
        <form class="mt-6 max-w-2xl" @submit.prevent="save">
            <FormRenderer :fields="fields" :form="form" />
            <div class="mt-6 flex gap-3">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="rounded-lg bg-accent px-4 py-2 text-sm font-medium text-white disabled:opacity-60"
                >Save changes</button>
                <Link :href="base" class="rounded-lg border border-line-2 px-4 py-2 text-sm">Cancel</Link>
            </div>
        </form>
    </PanelLayout>
</template>
