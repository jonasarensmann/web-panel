<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {
    NH1,
    NCard,
    NP,
    NButton,
    NSpace,
    NFlex,
    NPopconfirm,
    NModal,
    NInput,
    NBreadcrumbItem,
} from "naive-ui";
import { ref } from "vue";
import { Head, useForm } from "@inertiajs/vue3";

import { Token } from "@/types";

import CreateModal from "@/Components/ApiKeys/CreateModal.vue";
import ApiKeyTable from "@/Components/ApiKeys/ApiKeyTable.vue";

const props = defineProps({
    tokens: {
        type: Array as () => Token[],
        required: true,
    },
    token: {
        type: String,
        required: false,
    },
});

const createTokenModalVisible = ref(false);
const createdTokenModalVisible = ref(props.token ? true : false);

const deleteSelectedForm = useForm({
    tokens: [],
});

const deleteSelected = () => {
    deleteSelectedForm.delete(route("tokens.destroy"), {
        preserveScroll: true,
        preserveState: false,
        onSuccess: () => {
            deleteSelectedForm.reset();
        },
    });
};
</script>

<template>
    <Head title="API Keys" />

    <AuthenticatedLayout>
        <template #page-title>
            <n-h1>API Keys</n-h1>
        </template>

        <template #breadcrumb>
            <n-breadcrumb-item>Miscellaneous</n-breadcrumb-item>
            <n-breadcrumb-item>API Keys</n-breadcrumb-item>
        </template>

        <div>
            <n-modal
                v-model:show="createdTokenModalVisible"
                preset="dialog"
                title="Created API Key"
            >
                <n-p
                    >Here is your new API key. Please copy it and store it in a
                    safe place. You will not be able to see it again.</n-p
                >
                <n-input v-model:value="props.token" readonly class="mb-5" />
                <n-flex justify="end">
                    <n-button
                        type="primary"
                        @click="createdTokenModalVisible = false"
                    >
                        Close
                    </n-button>
                </n-flex>
            </n-modal>

            <n-card>
                <n-space vertical>
                    <n-p
                        >API keys allow third-party services to authenticate
                        with our application. They are used to authenticate
                        requests to the API.</n-p
                    >
                    <n-p class="mb-10"
                        >Each API key is unique and should be kept secret. If
                        you believe on of your API keys has been compromised,
                        you can remove it at any time.</n-p
                    >

                    <n-flex justify="end">
                        <n-button
                            type="primary"
                            @click="createTokenModalVisible = true"
                        >
                            Create API Key
                        </n-button>
                        <n-popconfirm
                            positive-text="Delete"
                            negative-text="Cancel"
                            @positive-click="deleteSelected"
                            placement="top-end"
                        >
                            <template #trigger>
                                <n-button
                                    :disabled="
                                        deleteSelectedForm.tokens.length < 1
                                            ? true
                                            : false
                                    "
                                >
                                    Delete Selected
                                </n-button>
                            </template>
                            Delete the selected API keys?
                        </n-popconfirm>
                    </n-flex>

                    <CreateModal
                        :visible="createTokenModalVisible"
                        @close="createTokenModalVisible = false"
                    />

                    <ApiKeyTable
                        :tokens="tokens"
                        @updateSelected="deleteSelectedForm.tokens = $event"
                    />
                </n-space>
            </n-card>
        </div>
    </AuthenticatedLayout>
</template>
