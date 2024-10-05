<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

import { Head } from "@inertiajs/vue3";
import { NH1, NBreadcrumbItem } from "naive-ui";
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #page-title>
            <n-h1>Dashboard</n-h1>
        </template>

        <template #breadcrumb>
            <n-breadcrumb-item>User</n-breadcrumb-item>
            <n-breadcrumb-item>Dashboard</n-breadcrumb-item>
        </template>
    </AuthenticatedLayout>
</template>
