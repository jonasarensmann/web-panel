<template>
    <n-modal
        :show="visible"
        preset="dialog"
        role="dialog"
        title="Add Domain"
        positive-text="Add"
        negative-text="Cancel"
        @positive-click="addDomain"
        @negative-click="
            form.reset();
            emit('close');
        "
    >
        <n-input
            type="text"
            v-model:value="form.domain"
            placeholder="Domain e.g. example.com"
            style="margin-top: 1rem"
            :status="form.errors.domain ? 'error' : undefined"
        />
        <n-p style="color: #e88080; margin-top: 0" v-if="form.errors.domain">{{
            form.errors.domain
        }}</n-p>
    </n-modal>
</template>

<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";
import { NModal, NInput, NP } from "naive-ui";

defineProps({
    visible: {
        type: Boolean,
        required: true,
    },
});

const emit = defineEmits(["close"]);

const form = useForm({
    domain: "",
});

let added = false;

const addDomain = () => {
    if (!form.domain) {
        form.errors.domain = "Domain is required";
        return;
    }

    if (added) {
        form.errors.domain = "Domain is already being added";
        return;
    }

    added = true;

    form.post(route("domains.store"), {
        preserveScroll: true,
        preserveState: false,
        onStart: () => {
            setTimeout(() => {
                form.reset();
                location.reload();
                emit("close");
            }, 3000);
        },
    });
};
</script>
