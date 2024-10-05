<template>
    <n-modal
        :show="visible"
        preset="dialog"
        role="dialog"
        :title="`Create ${createType === 'file' ? 'File' : 'Folder'}`"
        positive-text="Create"
        negative-text="Cancel"
        @positive-click="addFile"
        @negative-click="
            form.reset();
            emit('close');
        "
    >
        <n-p style="color: #e88080; margin-top: 0" v-if="form.errors.name">{{
            form.errors.name
        }}</n-p>
        <n-input
            type="text"
            v-model:value="form.name"
            placeholder="Name"
            style="margin-top: 1rem"
            :status="form.errors.name ? 'error' : undefined"
            @input="form.errors.name = undefined"
        />
        <n-p style="color: #e88080; margin-top: 0" v-if="form.errors.name">{{
            form.errors.name
        }}</n-p>
    </n-modal>
</template>

<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";
import { NModal, NInput, NP } from "naive-ui";

const props = defineProps({
    visible: {
        type: Boolean,
        required: true,
    },
    path: {
        type: String,
        required: true,
    },
    createType: {
        type: String,
        required: true,
    },
});

const emit = defineEmits(["close"]);

const form = useForm({
    name: "",
});

const addFile = () => {
    if (!form.name) {
        form.errors.name = "Name is required";
        return;
    }

    form.transform((data) => {
        return {
            path: props.path + "/" + data.name,
        };
    }).post(
        props.createType === "file"
            ? route("files.store")
            : route("files.storedir"),
        {
            preserveScroll: true,
            preserveState: false,
            onSuccess: () => {
                emit("close");
            },
        }
    );
};
</script>
