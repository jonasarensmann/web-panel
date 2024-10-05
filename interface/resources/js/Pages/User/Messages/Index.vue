<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Message } from "@/types";

import { Head, Link, router } from "@inertiajs/vue3";
import {
    NH1,
    NCard,
    NBreadcrumbItem,
    NButton,
    NDataTable,
    DataTableColumns,
} from "naive-ui";
import { h } from "vue";

const props = defineProps<{ messages: Message[] }>();

const getColorForType = (type: string) => {
    switch (type) {
        case "error":
            return "#dc3545";
        case "warning":
            return "#ffc107";
        case "info":
            return "#0d6efd";
        case "success":
            return "#198754";
        default:
            return "#0d6efd";
    }
};

const deleteMessage = (id: number) => {
    router.visit(route("messages.destroy", { message: id }), {
        method: "delete",
    });
};

function createColumns(): DataTableColumns<Message> {
    return [
        {
            title: "Id",
            key: "id",
        },
        {
            title: "title",
            key: "title",
            render(row) {
                return h(
                    Link,
                    {
                        href: route("messages.show", { message: row.id }),
                        style: {
                            color: getColorForType(row.type),
                            fontWeight: row.read ? "normal" : "bold",
                        },
                    },
                    {
                        default: () => `[${row.type}] ${row.title}`,
                    }
                );
            },
        },
        {
            title: "Action",
            key: "actions",
            render(row) {
                return h(
                    NButton,
                    {
                        strong: true,
                        tertiary: true,
                        size: "small",
                        onClick: () => deleteMessage(row.id),
                    },
                    { default: () => "Delete" }
                );
            },
        },
    ];
}

const columns = createColumns();
</script>

<template>
    <Head title="Messages" />

    <AuthenticatedLayout>
        <template #page-title>
            <n-h1>Messages</n-h1>
        </template>

        <template #breadcrumb>
            <n-breadcrumb-item>User</n-breadcrumb-item>
            <n-breadcrumb-item>Messages</n-breadcrumb-item>
        </template>

        <n-card>
            <n-data-table
                :columns="columns"
                :data="messages"
                :bordered="false"
            />
        </n-card>
    </AuthenticatedLayout>
</template>
