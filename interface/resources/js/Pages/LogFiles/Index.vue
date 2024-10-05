<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import FileTable from "@/Components/LogFiles/FileTable.vue";

import { Head } from "@inertiajs/vue3";
import { NH1, NCard, NBreadcrumbItem, NFlex } from "naive-ui";

interface file {
    [key: string]: string;
}

defineProps<{ files: file[] }>();
</script>

<template>
    <Head title="Log Files" />

    <AuthenticatedLayout>
        <template #page-title>
            <n-h1>Log Files</n-h1>
        </template>

        <template #breadcrumb>
            <n-breadcrumb-item>Information</n-breadcrumb-item>
            <n-breadcrumb-item>Log Files</n-breadcrumb-item>
        </template>

        <n-card>
            <file-table :files="files" />
        </n-card>
    </AuthenticatedLayout>
</template>
