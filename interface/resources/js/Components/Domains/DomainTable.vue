<template>
    <n-data-table :columns="columns" :data="data" :pagination="{ pageSize: 10 }"
    :row-key="(row: RowData) => row.id" @update:checked-row-keys="handleCheck" />
</template>

<script setup lang="ts">
import { Domain, Subdomain } from '@/types';
import { NDataTable } from 'naive-ui';
import type { DataTableColumns, DataTableRowKey } from 'naive-ui'
import { h } from 'vue';

import { Link } from '@inertiajs/vue3';

type RowData = {
    id: number;
    name: string;
    subdomains: string;
    lock_status: string;
};

const props = defineProps<{
    domains: Domain[];
    subdomains: Subdomain[];
}>();

const emit = defineEmits(['updateSelected']);

const createColumns = (): DataTableColumns<RowData> => [
    {
        type: 'selection',
    },
    {
        title: 'Domain',
        key: 'name',
        render(row) {
            return h(Link,
                {
                    href: route('domains.show', { domain: row.id })
                },
                { default: () => row.name }
            )
        }
    },
    {
        title: 'Subdomains',
        key: 'subdomains'
    },
    {
        title: 'Lock Status',
        key: 'lock_status'
    }
];

const columns = createColumns();

const handleCheck = (keys: DataTableRowKey[]) => {
    emit('updateSelected', keys);
};

const data: RowData[] = props.domains.map((domain: Domain) => {
    return {
        id: domain.id,
        name: domain.name,
        lock_status: domain.locked ? 'Locked' : 'Unlocked',
        subdomains: props.subdomains.filter((subdomain: Subdomain) => subdomain.domain_id === domain.id).map((subdomain: Subdomain) => subdomain.name).join(', ') || 'None',
    };
});
</script>
