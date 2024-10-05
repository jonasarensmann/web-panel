<script setup lang="ts">
import AddModal from "@/Components/Subdomains/AddModal.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Domain, Subdomain } from "@/types";

import { Head, Link, router } from "@inertiajs/vue3";
import {
    NH1,
    NH3,
    NCard,
    NDataTable,
    NBreadcrumbItem,
    NButton,
    NFlex,
    DataTableColumns,
} from "naive-ui";
import { h, ref } from "vue";

const props = defineProps<{ subdomains: Subdomain[]; domains: Domain[] }>();

const formattedDomains = props.domains.map((domain) => ({
    label: `.${domain.name}`,
    value: domain.id,
}));

const showAddModal = ref(false);

const createColumns = (): DataTableColumns<Subdomain> => {
    return [
        {
            title: "Name",
            key: "name",
        },
        {
            title: "Domain",
            key: "domain",
            render(row) {
                return props.domains.find((d) => d.id === row.domain_id)?.name;
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
                                    tertiary: true,
                                    onClick: () => {
                                        router.get(
                                            route("domains.show", {
                                                domain: row.domain_id,
                                            })
                                        );
                                    },
                                },
                                { default: () => "View" }
                            ),
                            h(
                                NButton,
                                {
                                    type: "error",
                                    onClick: () => {
                                        router.visit(
                                            route("subdomains.destroy", {
                                                subdomain: row.id,
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

const columns = createColumns();
</script>

<template>
    <Head title="Subdomains" />

    <AuthenticatedLayout>
        <template #page-title>
            <n-h1>Subdomains</n-h1>
        </template>

        <template #breadcrumb>
            <n-breadcrumb-item>Sites</n-breadcrumb-item>
            <n-breadcrumb-item>Subdomains</n-breadcrumb-item>
        </template>

        <n-card>
            <n-flex justify="end">
                <n-button type="primary" @click="showAddModal = true"
                    >Create Subdomain</n-button
                >
            </n-flex>

            <n-data-table class="mt-8" :columns="columns" :data="subdomains" />
        </n-card>

        <AddModal
            :visible="showAddModal"
            @close="showAddModal = false"
            :domains="formattedDomains"
        ></AddModal>
    </AuthenticatedLayout>
</template>
