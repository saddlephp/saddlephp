<script setup>
import { useForm, usePage, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import PanelLayout from '../../Components/PanelLayout.vue';

const { t } = useI18n();
const props = defineProps({ resource: Object, fields: Array });
const { saddle } = usePage().props;
const base = `/${saddle.path}/resources/${props.resource.uriKey}`;

const form = useForm({ file: null });

function submit() {
    form.post(`${base}/import`);
}
</script>

<template>
    <PanelLayout>
        <h1 class="text-2xl font-semibold tracking-tight">{{ t('actions.import') }} {{ resource.label }}</h1>
        <p class="mt-1 text-sm text-ink-2">CSV headers must match field names: {{ fields.join(', ') }}.</p>
        <form class="mt-6 max-w-lg" @submit.prevent="submit">
            <input type="file" accept=".csv,text/csv" @input="form.file = $event.target.files[0]" />
            <p v-if="form.errors.file" class="mt-1 text-xs text-accent">{{ form.errors.file }}</p>
            <div class="mt-6 flex gap-3">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="rounded-lg bg-accent px-4 py-2 text-sm font-medium text-white disabled:opacity-60"
                >{{ t('actions.import') }}</button>
                <Link :href="base" class="rounded-lg border border-line-2 px-4 py-2 text-sm">{{ t('actions.cancel') }}</Link>
            </div>
        </form>
    </PanelLayout>
</template>
