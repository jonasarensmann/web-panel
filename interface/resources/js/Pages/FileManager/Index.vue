<script setup lang="ts">
import AddModal from "@/Components/FileManager/AddModal.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { File } from "@/types";

import { Head, router } from "@inertiajs/vue3";
import { InsertDriveFileOutlined, FolderOutlined } from "@vicons/material";
import {
    NH1,
    NH3,
    NCard,
    NBreadcrumbItem,
    NList,
    NListItem,
    NButton,
    NFlex,
    NCheckbox,
    NIcon,
} from "naive-ui";
import { ref } from "vue";

const props = defineProps<{ files: File[]; path: string }>();

const sortedFiles = ref<File[]>(props.files);

const createModalVisible = ref(false);
const createType = ref<"file" | "dir">("file");

sortedFiles.value = props.files.sort((a, b) => {
    if (a.type === "directory" && b.type !== "directory") {
        return -1;
    }

    if (a.type !== "directory" && b.type === "directory") {
        return 1;
    }

    return a.name.localeCompare(b.name);
});

const showHiddenFiles = ref(false);

const viewDirectory = (directory: string) => {
    router.visit(route("files.index"), {
        method: "get",
        data: {
            path: `${props.path}/${directory}/`,
        },
    });
};

const goUp = () => {
    const splittedPath = props.path.split("/").filter((part) => part !== "");
    splittedPath.pop();

    const path = "/" + splittedPath.join("/");

    router.visit(route("files.index"), {
        method: "get",
        data: {
            path,
        },
    });
};

const viewFile = (file: string) => {
    file = `${props.path}/${file}`;

    router.visit(route("files.index"), {
        method: "get",
        data: {
            path: file,
        },
    });
};

const deleteFile = (file: string) => {
    file = `${props.path}/${file}`;

    router.visit(route("files.destroy"), {
        method: "delete",
        data: {
            path: file,
        },
    });
};
</script>

<template>

    <Head title="File Manager" />

    <AuthenticatedLayout>
        <template #page-title>
            <n-h1>File Manager</n-h1>
        </template>

        <template #breadcrumb>
            <n-breadcrumb-item>Sites</n-breadcrumb-item>
            <n-breadcrumb-item>File Manager</n-breadcrumb-item>
        </template>

        <n-card>
            <n-flex justify="end" align="center">
                <n-h3 style="margin: 0; margin-right: auto">{{ path }}</n-h3>
                <n-checkbox v-model:checked="showHiddenFiles">Show hidden files</n-checkbox>
                <n-button type="success" @click="createType = 'file'; createModalVisible = true">Create File</n-button>
                <n-button type="success" @click="createType = 'dir'; createModalVisible = true;">Create
                    Folder</n-button>
            </n-flex>

            <n-list style="margin-top: 1rem">
                <n-list-item v-if="path !== `/home/${$page.props.auth.user.username}`" style="cursor: pointer"
                    @click="goUp()">
                    <n-flex align="center">
                        <n-icon size="30">
                            <FolderOutlined />
                        </n-icon>
                        <p>..</p>
                    </n-flex>
                </n-list-item>

                <template v-for="file in files">
                    <n-list-item v-if="showHiddenFiles || !file.name.startsWith('.')">
                        <template #suffix>
                            <n-flex :wrap="false">
                                <n-button @click="
                                    file.type === 'directory'
                                        ? viewDirectory(file.name)
                                        : viewFile(file.name)
                                    " tertiary>View</n-button>
                                <n-button @click="deleteFile(file.name)" type="error">Delete</n-button>
                            </n-flex>
                        </template>
                        <template v-if="file.type === 'directory'">
                            <n-flex align="center">
                                <n-icon size="30">
                                    <FolderOutlined />
                                </n-icon>
                                <p style="cursor: pointer" @click="viewDirectory(file.name)">
                                    {{ file.name }}
                                </p>
                            </n-flex>
                        </template>
                        <template v-else>
                            <n-flex align="center">
                                <n-icon size="30">
                                    <InsertDriveFileOutlined />
                                </n-icon>
                                <p style="cursor: pointer" @click="viewFile(file.name)">
                                    {{ file.name }}
                                </p>
                            </n-flex>
                        </template>
                    </n-list-item>
                </template>
            </n-list>
        </n-card>

        <AddModal :visible="createModalVisible" :path="path" :createType="createType"
            @close="createModalVisible = false" />
    </AuthenticatedLayout>
</template>
