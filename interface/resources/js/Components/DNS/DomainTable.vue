<template>
    <n-data-table :columns="columns" :data="data" :pagination="{ pageSize: 10 }"
    :row-key="(row: RowData) => row.id" @update:checked-row-keys="handleCheck" />
</template>

<script setup lang="ts">
import { Domain } from '@/types';
import { NDataTable } from 'naive-ui';
import type { DataTableColumns, DataTableRowKey } from 'naive-ui'
import { h } from 'vue';

import { Link } from '@inertiajs/vue3';

type RowData = {
    id: number;
    name: string;
};

const props = defineProps<{
    domains: Domain[];
}>();

const emit = defineEmits(['updateSelected']);

const createColumns = (): DataTableColumns<RowData> => [
    {
        title: 'Domain',
        key: 'name',
        render(row) {
            return h(Link,
                {
                    href: route('dns.show', { domain: row.id })
                },
                { default: () => row.name }
            )
        }
    },
];

const columns = createColumns();

const handleCheck = (keys: DataTableRowKey[]) => {
    emit('updateSelected', keys);
};

const data: RowData[] = props.domains.map((domain: Domain) => {
    return {
        id: domain.id,
        name: domain.name,
    };
});
</script>
