<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

import { Head } from "@inertiajs/vue3";
import {
    NH1,
    NCard,
    NList,
    NListItem,
    NBreadcrumbItem,
    NFlex,
    NProgress,
} from "naive-ui";
import { ref } from "vue";

interface SystemInfo {
    php: {
        version: string;
        extensions: string[];
    };
    server: {
        software: string;
        name: string;
        os: string;
        release: string;
        machine: string;
    };
    laravel: {
        version: string;
        env: string;
    };
    cpu: {
        model: string;
    };
    memory: {
        total: string;
        free: string;
        used: string;
    };
}

const props = defineProps<{ info: SystemInfo }>();

const memUsed = ref(0);

const memUsedNum = props.info.memory.used.split(" ").filter((x) => x !== "")[1];
const memTotalNum = props.info.memory.total
    .split(" ")
    .filter((x) => x !== "")[1];

memUsed.value = Math.round(
    ((parseInt(memTotalNum) - parseInt(memUsedNum)) / parseInt(memTotalNum)) *
        100
);
</script>

<template>
    <Head title="System Information" />

    <AuthenticatedLayout>
        <template #page-title>
            <n-h1>System Information</n-h1>
        </template>

        <template #breadcrumb>
            <n-breadcrumb-item>Information</n-breadcrumb-item>
            <n-breadcrumb-item>System Information</n-breadcrumb-item>
        </template>

        <n-card>
            <n-flex justify="space-between">
                <n-flex>
                    <n-list>
                        <n-list-item>
                            <n-flex>
                                <p>PHP Version:</p>
                                {{ info.php.version }}
                            </n-flex>
                        </n-list-item>
                        <n-list-item>
                            <n-flex>
                                <p>PHP Extensions:</p>
                                {{ info.php.extensions.join(", ") }}
                            </n-flex>
                        </n-list-item>
                        <n-list-item>
                            <n-flex>
                                <p>Server Software:</p>
                                {{ info.server.software }}
                            </n-flex>
                        </n-list-item>
                        <n-list-item>
                            <n-flex>
                                <p>Hostname:</p>
                                {{ info.server.name }}
                            </n-flex>
                        </n-list-item>
                        <n-list-item>
                            <n-flex>
                                <p>OS:</p>
                                {{ info.server.os }}
                            </n-flex>
                        </n-list-item>
                        <n-list-item>
                            <n-flex>
                                <p>Kernel:</p>
                                {{ info.server.release }}
                            </n-flex>
                        </n-list-item>
                        <n-list-item>
                            <n-flex>
                                <p>Architecture:</p>
                                {{ info.server.machine }}
                            </n-flex>
                        </n-list-item>
                        <n-list-item>
                            <n-flex>
                                <p>Laravel Version:</p>
                                {{ info.laravel.version }}
                            </n-flex>
                        </n-list-item>
                        <n-list-item>
                            <n-flex>
                                <p>Laravel Environment:</p>
                                {{ info.laravel.env }}
                            </n-flex>
                        </n-list-item>
                        <n-list-item>
                            <n-flex>
                                <p>CPU:</p>
                                {{ info.cpu.model }}
                            </n-flex>
                        </n-list-item>
                        <n-list-item>
                            <n-flex>
                                <p>Memory:</p>
                                <n-progress
                                    type="circle"
                                    :percentage="memUsed"
                                />
                                <n-list>
                                    <n-list-item>
                                        {{ info.memory.total }}
                                    </n-list-item>
                                    <n-list-item>
                                        {{ info.memory.free }}
                                    </n-list-item>
                                    <n-list-item>
                                        {{ info.memory.used }}
                                    </n-list-item>
                                </n-list>
                            </n-flex>
                        </n-list-item>
                    </n-list>
                </n-flex>
            </n-flex>
        </n-card>
    </AuthenticatedLayout>
</template>
