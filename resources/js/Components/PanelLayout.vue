<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const rodeo = computed(() => page.props.rodeo);
const base = computed(() => `/${rodeo.value.path}`);
</script>

<template>
    <div class="flex min-h-screen">
        <aside class="flex w-60 shrink-0 flex-col border-r border-line bg-bg">
            <Link :href="base" class="flex items-center gap-2.5 border-b border-line px-5 py-4">
                <span class="grid h-7 w-7 place-items-center rounded-lg bg-ink text-xs font-bold text-white">R</span>
                <span class="font-semibold tracking-tight">{{ rodeo.name }}</span>
            </Link>
            <nav class="flex-1 p-3">
                <div v-for="(group, gi) in rodeo.nav" :key="gi" class="mb-4">
                    <p v-if="group.group" class="px-2 pb-1 text-[0.65rem] font-medium uppercase tracking-wide text-ink-3">{{ group.group }}</p>
                    <Link
                        v-for="item in group.items"
                        :key="item.uriKey"
                        :href="`${base}/resources/${item.uriKey}`"
                        class="mb-0.5 flex items-center rounded-md px-3 py-2 text-sm transition"
                        :class="item.active ? 'bg-ink font-medium text-white' : 'text-ink-2 hover:bg-surface-2'"
                    >{{ item.label }}</Link>
                </div>
            </nav>
            <div v-if="rodeo.user" class="border-t border-line px-5 py-3">
                <p class="text-xs font-medium">{{ rodeo.user.name }}</p>
                <p class="text-[0.65rem] text-ink-3">{{ rodeo.user.email }}</p>
            </div>
        </aside>

        <main class="min-w-0 flex-1 p-8">
            <div v-if="rodeo.flash.success" class="mb-5 rounded-lg border border-line bg-bg px-4 py-3 text-sm">
                {{ rodeo.flash.success }}
            </div>
            <slot />
        </main>
    </div>
</template>
