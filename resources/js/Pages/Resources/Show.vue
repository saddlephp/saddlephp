<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import PanelLayout from '../../Components/PanelLayout.vue';
import DisplayRenderer from '../../Components/DisplayRenderer.vue';
import RelationManagers from '../../Components/RelationManagers.vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({ resource: Object, record: Object, fields: Array, relations: Array });

const { saddle } = usePage().props;
const base = `/${saddle.path}/resources/${props.resource.uriKey}`;
</script>

<template>
    <PanelLayout>
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold tracking-tight">{{ record.title }}</h1>
            <div class="flex gap-3">
                <Link
                    v-if="record.can.update"
                    :href="`${base}/${record.id}/edit`"
                    class="rounded-lg bg-accent px-4 py-2 text-sm font-medium text-white"
                >{{ t('actions.edit') }}</Link>
                <Link :href="base" class="rounded-lg border border-line-2 px-4 py-2 text-sm">{{ t('actions.back') }}</Link>
            </div>
        </div>

        <div class="mt-6 max-w-2xl rounded-xl border border-line p-6">
            <DisplayRenderer :nodes="fields" />
        </div>

        <RelationManagers :relations="relations" :base="`${base}/${record.id}`" />
    </PanelLayout>
</template>
