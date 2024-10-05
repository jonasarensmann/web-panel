<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import AddModal from "@/Components/Email/AddModal.vue";

import { Domain } from "@/types";

import { Head, router } from "@inertiajs/vue3";
import {
    NH1,
    NCard,
    NDataTable,
    NBreadcrumbItem,
    NButton,
    NFlex,
    DataTableColumns,
} from "naive-ui";
import { h, ref } from "vue";

const props = defineProps<{
    domain: Domain;
    emails: String[];
    domains: Domain[];
}>();

const showAddModal = ref(false);

const createColumns = (): DataTableColumns<String> => {
    return [
        {
            title: "Name",
            key: "name",
            render(row) {
                return h("span", row as string);
            },
        },
        {
            title: "Actions",
            key: "actions",
            render(row) {
                return h(
                    NFlex,
                    {},
                    {
                        default: () => [
                            h(
                                NButton,
                                {
                                    type: "error",
                                    onClick: () => {
                                        router.visit(
                                            route("emails.destroy", {
                                                domain: props.domain.id,
                                                email: row,
                                            }),
                                            { method: "delete" }
                                        );
                                    },
                                },
                                { default: () => "Delete" }
                            ),
                        ],
                    }
                );
            },
        },
    ];
};

const formattedDomains = props.domains.map((domain) => ({
    label: domain.name,
    value: domain.id,
}));

const columns = createColumns();
</script>

<template>
    <Head :title="`Emails ${domain.name}`" />

    <AuthenticatedLayout>
        <template #page-title>
            <n-h1>Emails {{ domain.name }}</n-h1>
        </template>

        <template #breadcrumb>
            <n-breadcrumb-item>Sites</n-breadcrumb-item>
            <n-breadcrumb-item>Email Management</n-breadcrumb-item>
            <n-breadcrumb-item>Emails {{ domain.name }}</n-breadcrumb-item>
        </template>

        <n-card>
            <n-flex justify="end">
                <n-button type="primary" @click="showAddModal = true"
                    >Create Email</n-button
                >
            </n-flex>

            <n-data-table class="mt-8" :columns="columns" :data="emails" />
        </n-card>

        <AddModal
            :visible="showAddModal"
            @close="showAddModal = false"
            :domains="formattedDomains"
        />
    </AuthenticatedLayout>
</template>
