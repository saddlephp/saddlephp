<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import PanelLayout from '../../Components/PanelLayout.vue';
import FormRenderer from '../../Components/FormRenderer.vue';
import { flattenFields } from '../../support/flattenFields';

const { t } = useI18n();
const props = defineProps({ fields: Array });
const { saddle } = usePage().props;
const basePath = saddle.path.split('/')[0];

const form = useForm(Object.fromEntries(flattenFields(props.fields).map((f) => [f.name, f.value])));

function submit() {
    form.post(`/${basePath}/register`);
}
</script>

<template>
    <PanelLayout>
        <h1 class="text-2xl font-semibold tracking-tight">New workspace</h1>
        <form class="mt-6 max-w-lg" @submit.prevent="submit">
            <FormRenderer :fields="fields" :form="form" />
            <button
                type="submit"
                :disabled="form.processing"
                class="mt-6 rounded-lg bg-accent px-4 py-2 text-sm font-medium text-white disabled:opacity-60"
            >{{ t('actions.save') }}</button>
        </form>
    </PanelLayout>
</template>
