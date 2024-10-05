<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import DomainTable from "@/Components/DNS/DomainTable.vue";
import { Domain } from "@/types";

import { Head } from "@inertiajs/vue3";
import { NH1, NCard, NBreadcrumbItem } from "naive-ui";

defineProps<{ domains: Domain[] }>();
</script>

<template>
    <Head title="DNS Management" />

    <AuthenticatedLayout>
        <template #page-title>
            <n-h1>DNS Management</n-h1>
        </template>

        <template #breadcrumb>
            <n-breadcrumb-item>Sites</n-breadcrumb-item>
            <n-breadcrumb-item>DNS Management</n-breadcrumb-item>
        </template>

        <n-card>
            <DomainTable :domains="domains" />
        </n-card>
    </AuthenticatedLayout>
</template>
