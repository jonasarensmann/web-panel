<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

import { Head, useForm } from "@inertiajs/vue3";
import {
    NH1,
    NCard,
    NBreadcrumbItem,
    NInput,
    NButton,
    NFlex,
    NH3,
} from "naive-ui";
import { ref } from "vue";

const props = defineProps<{ name: string; content: string; path: string }>();

const goBack = () => {
    window.history.back();
};

const form = useForm({
    file_content: props.content,
    path: props.path,
})

const saveFile = () => {
    form.post(route('files.store'));
}
</script>

<template>

    <Head :title="`File: ${name}`" />

    <AuthenticatedLayout>
        <template #page-title>
            <n-h1>File: {{ name }}</n-h1>
        </template>

        <template #breadcrumb>
            <n-breadcrumb-item>Sites</n-breadcrumb-item>
            <n-breadcrumb-item>File Manager</n-breadcrumb-item>
            <n-breadcrumb-item>File: {{ name }}</n-breadcrumb-item>
        </template>

        <n-card>
            <n-h3>{{ path }}</n-h3>

            <n-input v-model:value="form.file_content" type="textarea" placeholder="Basic Textarea"
                style="min-height: 60vh" />

            <n-flex justify="end" style="margin-top: 1rem">
                <n-button @click="goBack()">Go back</n-button>
                <n-button type="success" :disabled="!form.file_content || form.file_content === props.content"
                    @click="saveFile()">Save</n-button>
            </n-flex>
        </n-card>
    </AuthenticatedLayout>
</template>
