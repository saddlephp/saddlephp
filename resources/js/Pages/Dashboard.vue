<script setup>
import { usePage, Link } from '@inertiajs/vue3';
import PanelLayout from '../Components/PanelLayout.vue';

const { saddle } = usePage().props;
</script>

<template>
    <PanelLayout>
        <h1 class="text-2xl font-semibold tracking-tight">Howdy{{ saddle.user ? `, ${saddle.user.name}` : '' }}.</h1>
        <p class="mt-1 text-ink-2">Pick a resource and get ridin'.</p>
        <div class="mt-6 grid max-w-3xl gap-3 sm:grid-cols-2">
            <template v-for="(group, gi) in saddle.nav" :key="gi">
                <Link
                    v-for="item in group.items"
                    :key="item.uriKey"
                    :href="`/${saddle.path}/resources/${item.uriKey}`"
                    class="rounded-xl border border-line bg-bg p-5 transition hover:border-line-2"
                >
                    <p class="font-medium">{{ item.label }}</p>
                </Link>
            </template>
        </div>
    </PanelLayout>
</template>
