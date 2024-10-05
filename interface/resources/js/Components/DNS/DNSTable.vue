<template>
    <n-data-table :columns="columns" :data="data"
        :row-key="(row: RowData) => `${row.type.toLocaleLowerCase()}-${row.name}`"
        @update:checked-row-keys="handleCheck" />

    <edit-modal :visible="editModalVisible" @close="editModalVisible = false" :domain="domain"
        :record="currentRecord" />
</template>

<script setup lang="ts">
import { Domain } from "@/types";
import { NButton, NDataTable } from "naive-ui";
import type { DataTableColumns, DataTableRowKey } from "naive-ui";
import { h, ref } from "vue";
import EditModal from "./EditModal.vue";

type RowData = {
    name: string;
    type: string;
    value: string;
    actions?: string;
};

const props = defineProps<{
    domain: Domain;
}>();

const emit = defineEmits(["updateSelected"]);

const editModalVisible = ref(false);
const currentRecord = ref<RowData | null>(null);

const createColumns = (): DataTableColumns<RowData> => [
    {
        type: "selection",
    },
    {
        title: "Name",
        key: "name",
    },
    {
        title: "Type",
        key: "type",
    },
    {
        title: "Value",
        key: "value",
    },
    {
        title: "Actions",
        key: "actions",
        render(row) {
            return h(
                NButton,
                {
                    tertiary: true,
                    onClick: () => {
                        currentRecord.value = row;
                        editModalVisible.value = true;
                    },
                },
                { default: () => "Edit" }
            );
        },
    },
];

const columns = createColumns();

const handleCheck = (keys: DataTableRowKey[]) => {
    emit("updateSelected", keys);
};

const dns = props.domain.dns;

const data: RowData[] = (() => {
    const _data: RowData[] = [];

    dns.a?.forEach((a) => {
        _data.push({
            name: a.name,
            type: "A",
            value: a.ip,
        });
    });
    dns.aaaa?.forEach((aaaa) => {
        _data.push({
            name: aaaa.name,
            type: "AAAA",
            value: aaaa.ip,
        });
    });
    dns.cname?.forEach((cname) => {
        _data.push({
            name: cname.name,
            type: "CNAME",
            value: cname.alias,
        });
    });
    dns.mx?.forEach((mx) => {
        _data.push({
            name: props.domain.name + ".",
            type: "MX",
            value: `${mx.preference} ${mx.host}`,
        });
    });
    dns.ns?.forEach((ns) => {
        _data.push({
            name: props.domain.name + ".",
            type: "NS",
            value: ns.host,
        });
    });
    dns.txt?.forEach((txt) => {
        _data.push({
            name: txt.name,
            type: "TXT",
            value: txt.txt,
        });
    });
    dns.ptr?.forEach((ptr) => {
        _data.push({
            name: ptr.name,
            type: "PTR",
            value: ptr.host,
        });
    });

    return _data;
})();
</script>
