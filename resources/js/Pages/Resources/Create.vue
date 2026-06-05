<script setup>
import { provide, reactive } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import PanelLayout from '../../Components/PanelLayout.vue';
import FormRenderer from '../../Components/FormRenderer.vue';
import { flattenFields, isFileField } from '../../support/flattenFields';

const props = defineProps({ resource: Object, fields: Array });

const { saddle } = usePage().props;
const base = `/${saddle.path}/resources/${props.resource.uriKey}`;

provide('saddleOptionsBase', `${base}/options`);

const leaves = flattenFields(props.fields);
const fileNames = leaves.filter(isFileField).map((field) => field.name);

// File keys initialize to null, never the stored path string (a path would fail
// the `file` rule). On create there is no stored file, so a null that survives
// posts as empty and ConvertEmptyStringsToNull makes it null again — the
// nullable file rule passes. A user pick replaces null with the File object.
const form = useForm(
    Object.fromEntries(leaves.map((field) => [field.name, isFileField(field) ? null : field.value])),
);

// Track which file fields the user touched so the transform can keep them.
const touchedFiles = reactive(new Set());
form.__touchFile = (name) => touchedFiles.add(name);

// Drop untouched null file keys from the payload (matters for edit; harmless on
// create). data() already only emits the initial keys, so this only prunes.
form.transform((data) => {
    const out = { ...data };
    for (const name of fileNames) {
        if (out[name] === null && !touchedFiles.has(name)) delete out[name];
    }
    return out;
});

function save() {
    form.post(base);
}
</script>

<template>
    <PanelLayout>
        <h1 class="text-2xl font-semibold tracking-tight">New {{ resource.singularLabel.toLowerCase() }}</h1>
        <form class="mt-6 max-w-2xl" @submit.prevent="save">
            <FormRenderer :fields="fields" :form="form" />
            <div class="mt-6 flex gap-3">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="rounded-lg bg-accent px-4 py-2 text-sm font-medium text-white disabled:opacity-60"
                >Save</button>
                <Link :href="base" class="rounded-lg border border-line-2 px-4 py-2 text-sm">Cancel</Link>
            </div>
        </form>
    </PanelLayout>
</template>
