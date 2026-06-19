<script setup>
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

const page = usePage();
const saddle = computed(() => page.props.saddle);
const base = computed(() => `/${saddle.value.path}`);
const notifications = computed(() => saddle.value.notifications ?? { unread: 0, items: [] });

const open = ref(false);

function markRead(item) {
    router.post(`${base.value}/notifications/${item.id}/read`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            if (item.url) router.visit(item.url);
        },
    });
}

function markAll() {
    router.post(`${base.value}/notifications/read-all`, {}, { preserveScroll: true });
}
</script>

<template>
    <div v-if="saddle.notifications" class="relative border-b border-line px-3 py-2">
        <button
            type="button"
            class="flex w-full items-center justify-between rounded-lg px-2 py-1.5 text-sm text-ink-2 hover:bg-surface-2"
            @click="open = !open"
        >
            <span class="flex items-center gap-2">
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9" />
                    <path d="M13.7 21a2 2 0 0 1-3.4 0" />
                </svg>
                Notifications
            </span>
            <span v-if="notifications.unread" class="rounded-full bg-accent px-1.5 py-0.5 text-[0.65rem] font-medium text-white">{{ notifications.unread }}</span>
        </button>

        <div v-if="open" class="absolute left-3 right-3 z-20 mt-1 max-h-96 overflow-y-auto rounded-lg border border-line bg-bg shadow-lg">
            <div class="flex items-center justify-between border-b border-line px-3 py-2">
                <span class="text-xs font-medium text-ink-3">Notifications</span>
                <button v-if="notifications.unread" type="button" class="text-xs text-accent" @click="markAll">Mark all read</button>
            </div>
            <p v-if="!notifications.items.length" class="px-3 py-4 text-center text-sm text-ink-3">All quiet.</p>
            <button
                v-for="item in notifications.items"
                :key="item.id"
                type="button"
                class="block w-full border-b border-line/60 px-3 py-2 text-left text-sm hover:bg-surface-2"
                :class="item.read ? 'text-ink-3' : 'font-medium'"
                @click="markRead(item)"
            >
                <span class="block">{{ item.message }}</span>
                <span class="block text-[0.65rem] text-ink-3">{{ item.at }}</span>
            </button>
        </div>
    </div>
</template>
