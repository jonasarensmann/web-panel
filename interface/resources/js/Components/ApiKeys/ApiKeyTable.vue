<template>
    <n-data-table :columns="columns" :data="data" :pagination="{ pageSize: 10 }"
    :row-key="(row: RowData) => row.id" @update:checked-row-keys="handleCheck" />
</template>

<script setup lang="ts">
import { Token } from '@/types';
import { NDataTable } from 'naive-ui';
import type { DataTableColumns, DataTableRowKey } from 'naive-ui'

type RowData = {
    id: number;
    name: string;
    last_used_at: string;
    expires_at: string;
    created_at: string;
    updated_at: string;
};

const props = defineProps<{
    tokens: Token[];
}>();

const emit = defineEmits(['updateSelected']);

const createColumns = (): DataTableColumns<RowData> => [
    {
        type: 'selection',
    },
    {
        title: 'Name',
        key: 'name'
    },
    {
        title: 'Last Used At',
        key: 'last_used_at'
    },
    {
        title: 'Expires At',
        key: 'expires_at'
    },
    {
        title: 'Created At',
        key: 'created_at'
    },
    {
        title: 'Updated At',
        key: 'updated_at'
    }
];

const columns = createColumns();

const handleCheck = (keys: DataTableRowKey[]) => {
    emit('updateSelected', keys);
};

const data: RowData[] = props.tokens.map((token: Token) => {
    return {
        id: token.id,
        name: token.name,
        last_used_at: token.last_used_at ? new Date(token.last_used_at).toLocaleString() : 'Never',
        expires_at: token.expires_at ? new Date(token.expires_at).toLocaleString() : 'Never',
        created_at: new Date(token.created_at).toLocaleString(),
        updated_at: new Date(token.updated_at).toLocaleString()
    }
});

</script>
