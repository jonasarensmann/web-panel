<template>
    <n-modal
        :show="visible"
        preset="dialog"
        role="dialog"
        title="Add Site"
        positive-text="Add"
        negative-text="Cancel"
        @positive-click="add"
        @negative-click="
            form.reset();
            emit('close');
        "
    >
        <n-input
            type="text"
            v-model:value="form.port"
            placeholder="Port or Empty for default"
            style="margin-top: 1rem"
            :status="form.errors.port ? 'error' : undefined"
        />
        <n-p style="color: #e88080; margin-top: 0" v-if="form.errors.port">{{
            form.errors.port
        }}</n-p>
    </n-modal>
</template>

<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";
import { NModal, NInput, NP, NCheckbox } from "naive-ui";
import { Domain } from "@/types";

const props = defineProps({
    visible: {
        type: Boolean,
        required: true,
    },
    domain: {
        type: Object as () => Domain,
        required: true,
    },
});

const emit = defineEmits(["close"]);

const form = useForm({
    port: "",
});

const add = () => {
    if (!form.port) {
        form.errors.port = "Port is required";
        return;
    }

    if (parseInt(form.port) < 0 || parseInt(form.port) > 65535) {
        form.errors.port = "Port must be between 0 and 65535";
        return;
    }

    form.post(route("caddyconfig.store", { domain: props.domain.id }), {
        preserveScroll: true,
        preserveState: false,
        onSuccess: () => {
            form.reset();
            emit("close");
        },
    });
};
</script>
