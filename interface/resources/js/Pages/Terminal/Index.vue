<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { onMounted } from "vue";
import { NH1, NCard, NBreadcrumbItem } from "naive-ui";
import { Head } from "@inertiajs/vue3";

const termSrc = `https://${window.location.host}/term/`;
</script>

<template>
    <Head title="Terminal" />

    <AuthenticatedLayout>
        <template #page-title>
            <n-h1>Terminal</n-h1>
        </template>

        <template #breadcrumb>
            <n-breadcrumb-item>Miscellaneous</n-breadcrumb-item>
            <n-breadcrumb-item>Terminal</n-breadcrumb-item>
        </template>

        <n-card>
            <iframe
                :src="termSrc"
                id="terminal"
                style="width: 100%; height: 60vh; border: none"
            ></iframe>
        </n-card>
    </AuthenticatedLayout>
</template>
