<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Message } from "@/types";

import { Head, router } from "@inertiajs/vue3";
import { NH1, NCard, NBreadcrumbItem, NButton, NText } from "naive-ui";

const props = defineProps<{ message: Message }>();

const deleteMessage = (id: number) => {
    router.visit(route("messages.destroy", { message: id }), {
        method: "delete",
    });
};

const removeUpdateBlock = (content: string) => {
    const startTag = "---STARTUPDATEBLOCK---";
    const endTag = "---ENDUPDATEBLOCK---";
    const startIndex = content.indexOf(startTag);
    const endIndex = content.indexOf(endTag);

    if (startIndex !== -1 && endIndex !== -1) {
        return (
            content.substring(0, startIndex) +
            content.substring(endIndex + endTag.length)
        );
    }
    return content;
};

const cleanedContent = removeUpdateBlock(props.message.content);
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
            <n-breadcrumb-item>{{ message.title }}</n-breadcrumb-item>
        </template>

        <n-card>
            <NH1>[{{ message.type }}] {{ message.title }}</NH1>

            <pre>{{ cleanedContent }}</pre>

            <div class="mt-8">
                <n-text depth="3">
                    Created: {{ new Date(message.created_at).toDateString() }}
                </n-text>
                <br />
                <n-button
                    class="mt-2"
                    tertiary
                    type="error"
                    @click="deleteMessage(message.id)"
                >
                    Delete
                </n-button>
            </div>
        </n-card>
    </AuthenticatedLayout>
</template>
