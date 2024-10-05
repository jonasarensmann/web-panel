<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import AddUModel from "@/Components/Domains/AddUModal.vue";
import { CaddyconfigEntry, Domain } from "@/types";

import { Head, useForm } from "@inertiajs/vue3";
import {
    NH1,
    NFlex,
    NSpace,
    NCard,
    NBreadcrumbItem,
    NTabs,
    NTabPane,
    NSelect,
    NInput,
    NCheckbox,
    NButton,
} from "naive-ui";
import { ref } from "vue";

const props = defineProps<{
    domain: Domain;
    username: string;
    error?: string;
}>();

const options = [
    {
        label: "File Server",
        value: "file_server",
    },
    {
        label: "Reverse Proxy",
        value: "reverse_proxy",
    },
    {
        label: "Static Response",
        value: "static_response",
    },
];

const currentTab = ref();
const addModalVisible = ref(false);

interface currentSelected {
    [key: string]: {
        mode: string;
        pending_changes: boolean;
    };
}
const currentSelected = ref<currentSelected>({});

const caddyconfig = props.domain.caddyconfig;
const filteredCaddyConfig: { [key: string]: CaddyconfigEntry } = {};

for (const [index, config] of Object.entries(caddyconfig)) {
    if (index !== "*." + props.domain.name) {
        filteredCaddyConfig[index as string] = config;
        continue;
    }
    if (index === "*." + props.domain.name) {
        for (const [subIndex, subConfig] of Object.entries(config)) {
            if (subIndex === "handle@webmail" || subIndex.startsWith("@")) {
                continue;
            }
            const newSubIndex = subIndex.split("@")[1];
            filteredCaddyConfig[newSubIndex + "." + props.domain.name] =
                subConfig;
        }
    }
}

for (const [index, config] of Object.entries(filteredCaddyConfig)) {
    if (config.file_server) {
        currentSelected.value[index] = {
            mode: "file_server",
            pending_changes: false,
        };
    } else if (config.reverse_proxy) {
        currentSelected.value[index] = {
            mode: "reverse_proxy",
            pending_changes: false,
        };
    } else if (config.respond) {
        currentSelected.value[index] = {
            mode: "static_response",
            pending_changes: false,
        };
    }
}

const form = useForm({
    port: 0,
    file_server: undefined as boolean | undefined,
    php: false,
    root: "",
    browse: false,
    reverse_proxy: undefined as boolean | undefined,
    reverse_proxy_location: "",
    static_response: undefined as boolean | undefined,
    static_response_text: "",
});

const initializeList = (index: string) => {
    const config = filteredCaddyConfig[index];

    form.file_server = config.file_server ? true : false;
    form.browse = config.file_server === "browse" ? true : false;
    form.php = config.php_fastcgi ? true : false;
    form.root = config.root
        ? config.root![1]
        : `/home/${props.username}/domains/${index}/public`;

    form.reverse_proxy = config.reverse_proxy ? true : false;
    form.reverse_proxy_location = config.reverse_proxy ?? "";

    form.static_response = config.respond ? true : false;

    form.static_response_text = config.respond?.replace(/^"|"$/g, "") ?? "";

    if (!index.split(":")[1]) {
        form.port = 0;
        return;
    }

    form.port = Number(index.split(":")[1]) ?? 0;
};

initializeList(Object.keys(filteredCaddyConfig)[0]);

const updateConfig = (index: string | number) => {
    form.patch(
        route("caddyconfig.update", { domain: props.domain.id, index: index }),
        {
            onFinish: () => {
                currentSelected.value[index].pending_changes = false;
            },
        }
    );
};

const handleInputChanges = (index: string, key: string, value: unknown) => {
    currentSelected.value[index].pending_changes = true;

    if (key === "mode") {
        if (value === "file_server") {
            form.file_server = true;
            form.reverse_proxy = false;
            form.static_response = false;
        } else if (value === "reverse_proxy") {
            form.reverse_proxy = true;
            form.file_server = false;
            form.static_response = false;
        } else if (value === "static_response") {
            form.static_response = true;
            form.file_server = false;
            form.reverse_proxy = false;
        }
        currentSelected.value[index].mode = value as string;
    }

    if (key === "browse") {
        form.browse = value as boolean;
    }

    if (key === "php") {
        form.php = value as boolean;
    }

    if (key === "reverse_proxy_location") {
        form.reverse_proxy_location = value as string;
    }

    if (key === "static_response_text") {
        form.static_response_text = value as string;
    }
};

const handleTabChange = (tab: string) => {
    currentTab.value = tab;
    initializeList(tab);
};

const deleteSite = () => {
    form.delete(route("caddyconfig.destroy", { domain: props.domain.id }), {
        preserveState: false,
    });
};
</script>

<template>
    <Head :title="domain.name" />

    <AuthenticatedLayout>
        <template #page-title>
            <n-h1>Domain - {{ domain.name }}</n-h1>
        </template>

        <template #breadcrumb>
            <n-breadcrumb-item>Sites</n-breadcrumb-item>
            <n-breadcrumb-item :href="route('domains.index')"
                >Domains</n-breadcrumb-item
            >
            <n-breadcrumb-item>{{ domain.name }}</n-breadcrumb-item>
        </template>

        <n-card>
            <n-space vertical>
                <n-flex justify="end">
                    <n-button type="primary" @click="addModalVisible = true"
                        >Create new</n-button
                    >
                </n-flex>

                <NH1 v-if="error">{{ error }}</NH1>

                <n-tabs
                    type="line"
                    animted
                    @update:value="handleTabChange($event)"
                    :value="currentTab"
                >
                    <n-tab-pane
                        v-for="(config, index) in filteredCaddyConfig"
                        :key="index"
                        :name="index"
                        :tab="`${index}${
                            currentSelected[index].pending_changes ? ' *' : ''
                        }`"
                    >
                        <n-space vertical>
                            <n-select
                                @update:value="
                                    handleInputChanges(
                                        index.toString(),
                                        'mode',
                                        $event
                                    )
                                "
                                :value="currentSelected[index].mode"
                                :options="options"
                            />

                            <template
                                v-if="
                                    currentSelected[index].mode ===
                                    'file_server'
                                "
                            >
                                <br />
                                <n-input v-model:value="form.root" readonly />
                                <n-space vertical>
                                    <n-checkbox
                                        @update:checked="
                                            (checked) => {
                                                form.browse = checked;
                                            }
                                        "
                                        :checked="form.browse"
                                        label="File Browsing enabled"
                                    />
                                    <n-checkbox
                                        v-model:checked="form.php"
                                        label="PHP Enabled"
                                    />
                                </n-space>
                            </template>
                            <template
                                v-else-if="
                                    currentSelected[index].mode ===
                                    'reverse_proxy'
                                "
                            >
                                <br />
                                <n-space vertical>
                                    <n-input
                                        v-model:value="
                                            form.reverse_proxy_location
                                        "
                                        placeholder="Reverse Proxy URL"
                                    />
                                </n-space>
                            </template>
                            <template
                                v-else-if="
                                    currentSelected[index].mode ===
                                    'static_response'
                                "
                            >
                                <br />
                                <n-space vertical>
                                    <n-input
                                        v-model:value="
                                            form.static_response_text
                                        "
                                        placeholder="Static Response"
                                    />
                                </n-space>
                            </template>

                            <br />

                            <n-space>
                                <n-button
                                    type="primary"
                                    @click="updateConfig(index)"
                                >
                                    Update
                                </n-button>
                                <n-button type="error" @click="deleteSite"
                                    >Delete</n-button
                                >
                            </n-space>
                        </n-space>
                    </n-tab-pane>
                </n-tabs>
            </n-space>
        </n-card>

        <AddUModel
            :visible="addModalVisible"
            @close="addModalVisible = false"
            :domain="domain"
        />
    </AuthenticatedLayout>
</template>
