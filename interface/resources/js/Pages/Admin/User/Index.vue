<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { User } from "@/types";

import { Head, router } from "@inertiajs/vue3";
import {
    NH1,
    NCard,
    NBreadcrumbItem,
    NDataTable,
    DataTableColumns,
    NFlex,
    NButton,
} from "naive-ui";
import { h, ref } from "vue";
import UserModal from "@/Components/Admin/UserModal.vue";

defineProps<{ users: User[] }>();

const createColumns = (): DataTableColumns<User> => {
    return [
        {
            title: "Id",
            key: "id",
        },
        {
            title: "Username",
            key: "username",
        },
        {
            title: "Is Admin",
            key: "is_admin",
            render(row) {
                return row.is_admin === 1 ? "true" : "false";
            },
        },
        {
            title: "Actions",
            key: "actions",
            render(row) {
                return h(
                    NFlex,
                    { justify: "end" },
                    {
                        default: () => [
                            h(
                                NButton,
                                {
                                    tertiary: true,
                                    onClick: () => {
                                        updateUser.value = true;
                                        currentId.value = row.id;
                                        currentUsername.value = row.username;
                                        currentIsAdmin.value =
                                            row.is_admin === 1;
                                        showCreateModal.value = true;
                                    },
                                },
                                { default: () => "Edit" }
                            ),
                            h(
                                NButton,
                                {
                                    type: "error",
                                    onClick: () => {
                                        confirm(
                                            "Are you sure you want to delete this user?"
                                        ) &&
                                            router.visit(
                                                route("users.destroy", {
                                                    user: row.id,
                                                }),
                                                {
                                                    method: "delete",
                                                }
                                            );
                                    },
                                },
                                { default: () => "Delete" }
                            ),
                        ],
                    }
                );
            },
        },
    ];
};

const updateUser = ref(false);
const currentId = ref(0);
const currentUsername = ref("");
const currentIsAdmin = ref(false);

const showCreateModal = ref(false);

const createUser = () => {
    updateUser.value = false;
    showCreateModal.value = true;
};

const columns = createColumns();
</script>

<template>
    <Head title="Users" />

    <AuthenticatedLayout>
        <template #page-title>
            <n-h1>Users</n-h1>
        </template>

        <template #breadcrumb>
            <n-breadcrumb-item>Admin</n-breadcrumb-item>
            <n-breadcrumb-item>Users</n-breadcrumb-item>
        </template>

        <n-card>
            <n-flex justify="end" class="mb-4">
                <n-button @click="createUser">Create User</n-button>
            </n-flex>
            <n-data-table :columns="columns" :data="users" />
        </n-card>

        <user-modal
            :visible="showCreateModal"
            @close="showCreateModal = false"
            v-if="!updateUser"
        />
        <user-modal
            :visible="showCreateModal"
            @close="showCreateModal = false"
            :id="currentId"
            :username="currentUsername"
            :is_admin="currentIsAdmin"
            v-if="updateUser"
        />
    </AuthenticatedLayout>
</template>
