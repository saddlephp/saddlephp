<script setup>
import { provide, reactive } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import PanelLayout from '../../Components/PanelLayout.vue';
import FormRenderer from '../../Components/FormRenderer.vue';
import { flattenFields, isFileField } from '../../support/flattenFields';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

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

// Inertia serializes the payload as FormData the moment a File is present, and
// it dispatches the verb it was given -- it does not spoof the method. PHP only
// parses multipart bodies on POST, so a real PUT arrives with $_POST and $_FILES
// both empty: every required field fails validation and the upload is dropped.
// Posting with _method keeps Laravel routing to the PUT handler.
const isUploading = () => fileNames.some((name) => form[name] instanceof File);

form.transform((data) => {
    const out = { ...data };
    for (const name of fileNames) {
        if (out[name] === null && !touchedFiles.has(name)) delete out[name];
    }
    if (fileNames.some((name) => out[name] instanceof File)) {
        out._method = 'put';
    }
    return out;
});

function save() {
    const url = `${base}/${props.record.id}`;

    isUploading() ? form.post(url) : form.put(url);
}
</script>

<template>
    <Head :title="'Edit ' + record.title" />

    <PanelLayout>
        <h1 class="text-2xl font-semibold tracking-tight">Edit {{ record.title }}</h1>
        <form class="mt-6 max-w-2xl" @submit.prevent="save">
            <FormRenderer :fields="fields" :form="form" />
            <div class="mt-6 flex gap-3">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="rounded-lg bg-accent px-4 py-2 text-sm font-medium text-white disabled:opacity-60"
                >{{ t('actions.save') }}</button>
                <Link :href="base" class="rounded-lg border border-line-2 px-4 py-2 text-sm">{{ t('actions.cancel') }}</Link>
            </div>
        </form>
    </PanelLayout>
</template>
