<script setup>
import { computed, ref } from 'vue';
import { flattenFields } from '../../support/flattenFields';
import FormRenderer from '../FormRenderer.vue';

const props = defineProps({ node: Object, form: Object });

// Only tabs that actually contain leaf fields are shown.
const visibleTabs = computed(() =>
    (props.node.tabs ?? [])
        .map((tab, index) => ({ tab, index, leaves: flattenFields(tab.schema) }))
        .filter((entry) => entry.leaves.length > 0),
);

const active = ref(0);

// True when any leaf inside the tab currently has a validation error, so we can
// flag the tab button even while the user is looking at a different tab.
function hasError(entry) {
    const errors = props.form?.errors ?? {};
    return entry.leaves.some((leaf) => errors[leaf.name]);
}
</script>

<template>
    <div v-if="visibleTabs.length">
        <div class="flex flex-wrap gap-1 border-b border-line" role="tablist">
            <button
                v-for="(entry, position) in visibleTabs"
                :key="entry.index"
                type="button"
                role="tab"
                :aria-selected="active === position"
                :class="[
                    'relative -mb-px border-b-2 px-3 py-2 text-sm font-medium transition',
                    active === position ? 'border-accent text-ink' : 'border-transparent text-ink-3 hover:text-ink-2',
                ]"
                @click="active = position"
            >
                {{ entry.tab.label }}
                <span
                    v-if="hasError(entry)"
                    class="absolute right-0.5 top-1 h-1.5 w-1.5 rounded-full bg-accent"
                    aria-label="Has errors"
                ></span>
            </button>
        </div>
        <div
            v-for="(entry, position) in visibleTabs"
            v-show="active === position"
            :key="entry.index"
            role="tabpanel"
            class="pt-5"
        >
            <FormRenderer :fields="entry.tab.schema" :form="form" />
        </div>
    </div>
</template>
