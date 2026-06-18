<script setup>
import RelationManagerTable from './RelationManagerTable.vue';

defineProps({ relations: Array, base: String });
</script>

<template>
    <div v-if="relations.length" class="mt-8 space-y-6">
        <RelationManagerTable v-for="rel in relations" :key="rel.key" :relation="rel" :base="base" />
    </div>
</template>
