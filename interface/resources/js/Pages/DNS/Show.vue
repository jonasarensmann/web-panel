<script setup lang="ts">
import DNSTable from "@/Components/DNS/DNSTable.vue";
import CreateModal from "@/Components/DNS/CreateModal.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Domain } from "@/types";

import { Head, useForm } from "@inertiajs/vue3";
import {
    NH1,
    NH3,
    NCard,
    NButton,
    NFlex,
    NSpace,
    NPopconfirm,
    NBreadcrumbItem,
} from "naive-ui";
import { ref } from "vue";

const props = defineProps<{ domain: Domain }>();

const createRecordModalVisible = ref(false);

const deleteSelectedForm = useForm({
    records: [],
});

const deleteSelected = () => {
    deleteSelectedForm.delete(route("dns.destroy", props.domain.id), {
        preserveScroll: true,
        preserveState: false,
        onSuccess: () => {
            deleteSelectedForm.reset();
        },
    });
};
</script>

<template>
    <Head :title="`DNS - ${domain.name}`" />

    <AuthenticatedLayout>
        <template #page-title>
            <n-h1>DNS - {{ domain.name }}</n-h1>
        </template>

        <template #breadcrumb>
            <n-breadcrumb-item>Sites</n-breadcrumb-item>
            <n-breadcrumb-item :href="route('dns.index')"
                >DNS</n-breadcrumb-item
            >
            <n-breadcrumb-item>{{ domain.name }}</n-breadcrumb-item>
        </template>

        <n-card>
            <n-space vertical>
                <n-h3>Records</n-h3>

                <n-flex justify="end" style="margin-top: 1rem">
                    <n-button
                        type="primary"
                        @click="createRecordModalVisible = true"
                        >Add Record</n-button
                    >
                    <n-popconfirm
                        positive-text="Delete"
                        negative-text="Cancel"
                        @positive-click="deleteSelected"
                        placement="top-end"
                    >
                        <template #trigger>
                            <n-button
                                :disabled="
                                    deleteSelectedForm.records.length < 1
                                        ? true
                                        : false
                                "
                            >
                                Delete Selected
                            </n-button>
                        </template>
                        Delete the selected API keys?
                    </n-popconfirm>
                </n-flex>

                <CreateModal
                    :visible="createRecordModalVisible"
                    :domain="domain"
                    @close="createRecordModalVisible = false"
                />

                <DNSTable
                    :domain="domain"
                    @updateSelected="deleteSelectedForm.records = $event"
                />
            </n-space>
        </n-card>
    </AuthenticatedLayout>
</template>
