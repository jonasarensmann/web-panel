<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

import { Head } from "@inertiajs/vue3";
import { NH1, NCard, NBreadcrumbItem, NLog } from "naive-ui";

defineProps<{ title: string; content: string }>();
</script>

<template>
    <Head :title="`Log ${title}`" />

    <AuthenticatedLayout>
        <template #page-title>
            <n-h1>Log {{ title }}</n-h1>
        </template>

        <template #breadcrumb>
            <n-breadcrumb-item>Information</n-breadcrumb-item>
            <n-breadcrumb-item>Log {{ title }}</n-breadcrumb-item>
        </template>

        <n-card>
            <n-log style="height: 70vh" :log="content" />
        </n-card>
    </AuthenticatedLayout>
</template>
