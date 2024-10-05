<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

import { Head, router } from "@inertiajs/vue3";
import {
    NH1,
    NCard,
    NList,
    NListItem,
    NBreadcrumbItem,
    NThing,
    NButton,
    NH3,
    NPopconfirm,
} from "naive-ui";

interface Update {
    id: string;
    message: string;
    date: string;
}

defineProps<{ updates: Update[] }>();

const update = () => {
    router.visit("/updates", {
        method: "post",
        onFinish: () => {
            sessionStorage.setItem("update", "false");
            location.reload();
        },
    });
};
</script>

<template>
    <Head title="Updates" />

    <AuthenticatedLayout>
        <template #page-title>
            <n-h1>Updates</n-h1>
        </template>

        <template #breadcrumb>
            <n-breadcrumb-item>Admin</n-breadcrumb-item>
            <n-breadcrumb-item>Updates</n-breadcrumb-item>
        </template>

        <n-card>
            <n-h3>{{ updates.length }} Updates available</n-h3>

            <n-list>
                <n-list-item v-for="update in updates">
                    <n-thing
                        :title="update.id"
                        content-style="margin-top: 10px;"
                    >
                        <template #footer>
                            {{ update.date }}
                        </template>
                        {{ update.message }}
                    </n-thing>
                </n-list-item>
            </n-list>

            <n-popconfirm @positive-click="update">
                <template #trigger>
                    <n-button type="primary" style="margin-top: 20px">
                        Update
                    </n-button>
                </template>
                Are you sure you want to update?
            </n-popconfirm>
        </n-card>
    </AuthenticatedLayout>
</template>
