<template>
    <n-data-table
        :columns="columns"
        :data="data"
        :pagination="{ pageSize: 10 }"
        :row-key="(row: file) => row.name"
        @update:checked-row-keys="handleCheck"
    />
</template>

<script setup lang="ts">
import { create, NDataTable } from "naive-ui";
import type { DataTableColumns, DataTableRowKey } from "naive-ui";
import { h } from "vue";

import { Link } from "@inertiajs/vue3";

interface file {
    [key: string]: string;
}

const props = defineProps<{
    files: object;
}>();

const emit = defineEmits(["updateSelected"]);

const createColumns = (): DataTableColumns<file> => [
    {
        title: "File",
        key: "name",
        render(row) {
            return h(
                Link,
                {
                    href: route("logs.show", { file: Object.keys(row)[0] }),
                },
                { default: () => Object.keys(row)[0] }
            );
        },
    },
];

const columns = createColumns();

const handleCheck = (keys: DataTableRowKey[]) => {
    emit("updateSelected", keys);
};

const data = Object.entries(props.files).map(([name, content]) => ({
    [name]: content,
}));
</script>
