<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3';
import PanelLayout from '../../Components/PanelLayout.vue';
import FormRenderer from '../../Components/FormRenderer.vue';

const props = defineProps({ resource: Object, record: Object, fields: Array });

const { rodeo } = usePage().props;
const base = `/${rodeo.path}/resources/${props.resource.uriKey}`;

const form = useForm(Object.fromEntries(props.fields.map((field) => [field.name, field.value])));
</script>

<template>
    <PanelLayout>
        <h1 class="text-2xl font-semibold tracking-tight">Edit {{ record.title }}</h1>
        <form class="mt-6 max-w-2xl" @submit.prevent="form.put(`${base}/${record.id}`)">
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
