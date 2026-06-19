<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import GlobalSearch from './GlobalSearch.vue';
import NotificationBell from './NotificationBell.vue';

const page = usePage();
const saddle = computed(() => page.props.saddle);
const base = computed(() => `/${saddle.value.path}`);

// Show a banner when the published panel assets lag behind the installed package.
const assetsStale = computed(() => {
    if (typeof __SADDLE_VERSION__ === 'undefined') return false;
    const serverVer = page.props.saddle?.version;
    return serverVer && serverVer !== __SADDLE_VERSION__;
});

function switchTenant(event) {
    const base = saddle.value.path.split('/').slice(0, -1).join('/');
    window.location.href = '/' + base + '/' + event.target.value;
}
</script>

<template>
    <div class="flex min-h-screen">
        <aside class="flex w-60 shrink-0 flex-col border-r border-line bg-bg">
            <Link :href="base" class="flex items-center gap-2.5 border-b border-line px-5 py-4">
                <img :src="'/vendor/saddle/icon.png'" alt="" class="h-7 w-7 shrink-0 rounded-lg" />
                <span class="font-semibold tracking-tight">{{ saddle.name }}</span>
            </Link>
            <GlobalSearch />
            <NotificationBell />
            <div v-if="saddle.tenants?.length > 1" class="border-b border-line px-3 py-2">
                <select
                    :value="saddle.tenant.key"
                    aria-label="Switch tenant"
                    class="w-full rounded-lg border border-line-2 bg-bg px-3 py-1.5 text-sm"
                    @change="switchTenant"
                >
                    <option v-for="t in saddle.tenants" :key="t.key" :value="t.key">{{ t.label }}</option>
                </select>
            </div>
            <nav class="flex-1 p-3">
                <div v-for="(group, gi) in saddle.nav" :key="gi" class="mb-4">
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
            <div v-if="saddle.user" class="border-t border-line px-5 py-3">
                <p class="text-xs font-medium">{{ saddle.user.name }}</p>
                <p class="text-[0.65rem] text-ink-3">{{ saddle.user.email }}</p>
            </div>
        </aside>

        <main class="min-w-0 flex-1 p-8">
            <div
                v-if="assetsStale"
                class="mb-5 rounded-lg border border-amber-300 bg-amber-50 px-4 py-2.5 text-sm text-amber-800"
            >
                Panel assets are out of date. Run <code class="font-mono font-medium">php artisan saddle:upgrade</code>.
            </div>
            <div v-if="saddle.flash.success" class="mb-5 rounded-lg border border-line bg-bg px-4 py-3 text-sm">
                {{ saddle.flash.success }}
            </div>
            <slot />
        </main>
    </div>
</template>
