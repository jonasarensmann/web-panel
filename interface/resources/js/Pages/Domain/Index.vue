<script setup lang="ts">
import AddModal from "@/Components/Domains/AddModal.vue";
import DomainTable from "@/Components/Domains/DomainTable.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Domain, Subdomain } from "@/types";

import { Head, router } from "@inertiajs/vue3";
import {
    NH1,
    NCard,
    NP,
    NButton,
    NSpace,
    NFlex,
    NPopconfirm,
    NBreadcrumbItem,
} from "naive-ui";
import { ref } from "vue";

const props = defineProps<{ domains: Domain[]; subdomains: Subdomain[] }>();

const addDomainModalVisible = ref(false);

const selectedItems = ref([]);

const lockSelected = () => {
    selectedItems.value.forEach((id: number) => {
        router.put(
            route("domains.update", id),
            {
                locked: props.domains.find((domain: Domain) => domain.id === id)
                    ?.locked
                    ? false
                    : true,
            },
            {
                preserveState: false,
                onFinish: () => {
                    selectedItems.value = [];
                },
            }
        );
    });
};

const removeSelected = () => {
    selectedItems.value.forEach((id: number) => {
        router.delete(route("domains.destroy", id), {
            preserveState: false,
            onFinish: () => {
                selectedItems.value = [];
            },
        });
    });
};
</script>

<template>
    <Head title="Domains" />

    <AuthenticatedLayout>
        <template #page-title>
            <n-h1>Domains</n-h1>
        </template>

        <template #breadcrumb>
            <n-breadcrumb-item>Sites</n-breadcrumb-item>
            <n-breadcrumb-item>Domains</n-breadcrumb-item>
        </template>

        <div>
            <n-card>
                <n-space vertical>
                    <n-flex justify="end">
                        <n-button
                            type="primary"
                            @click="addDomainModalVisible = true"
                        >
                            Add Domain
                        </n-button>
                        <n-popconfirm
                            positive-text="Confirm"
                            negative-text="Cancel"
                            @positive-click="lockSelected"
                            placement="top-end"
                        >
                            <template #trigger>
                                <n-button :disabled="selectedItems.length < 1">
                                    Lock/Unlock Selected
                                </n-button>
                            </template>
                            Are you sure you want to lock/unlock the selected
                            domains?
                        </n-popconfirm>
                        <n-popconfirm
                            positive-text="Remove"
                            negative-text="Cancel"
                            @positive-click="removeSelected"
                            placement="top"
                        >
                            <template #trigger>
                                <n-button :disabled="selectedItems.length < 1">
                                    Remove Selected
                                </n-button>
                            </template>
                            Are you sure you want to remove the selected
                            domains?
                        </n-popconfirm>
                    </n-flex>

                    <AddModal
                        :visible="addDomainModalVisible"
                        @close="addDomainModalVisible = false"
                    />

                    <DomainTable
                        :domains="domains"
                        :subdomains="subdomains"
                        @updateSelected="selectedItems = $event"
                    />
                </n-space>
            </n-card>
        </div>
    </AuthenticatedLayout>
</template>
