<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Domain, Subdomain } from "@/types";

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
import { h } from "vue";

defineProps<{ domains: Domain[] }>();

const createColumns = (): DataTableColumns<Domain> => {
    return [
        {
            title: "Name",
            key: "name",
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
                                            route("emails.show", {
                                                domain: row.id,
                                            })
                                        );
                                    },
                                },
                                { default: () => "View" }
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
    <Head title="Email Management" />

    <AuthenticatedLayout>
        <template #page-title>
            <n-h1>Email Management</n-h1>
        </template>

        <template #breadcrumb>
            <n-breadcrumb-item>Sites</n-breadcrumb-item>
            <n-breadcrumb-item>Email Management</n-breadcrumb-item>
        </template>

        <n-card>
            <n-data-table class="mt-8" :columns="columns" :data="domains" />
        </n-card>
    </AuthenticatedLayout>
</template>
