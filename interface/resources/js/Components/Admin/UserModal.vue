<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";
import { NModal, NInput, NP, NCheckbox } from "naive-ui";

const props = defineProps({
    visible: {
        type: Boolean,
        required: true,
    },
    username: String,
    is_admin: Boolean,
    id: Number,
});

const emit = defineEmits(["close"]);

const form = useForm({
    username: props.username,
    password: "",
    is_admin: props.is_admin,
});

const add = () => {
    if (!form.username) {
        form.errors.username = "Username is required";
        return;
    }

    if (!props.id) {
        if (!form.password) {
            form.errors.password = "Password is required";
            return;
        }

        form.post(route("users.store"), {
            preserveScroll: true,
            preserveState: false,
            onSuccess: () => {
                form.reset();
                emit("close");
            },
        });
    } else {
        form.patch(route("users.update", { user: props.id }), {
            preserveScroll: true,
            preserveState: false,
            onSuccess: () => {
                form.reset();
                emit("close");
            },
        });
    }
};
</script>

<template>
    <n-modal
        :show="visible"
        preset="dialog"
        role="dialog"
        :title="!id ? 'Add User' : 'Edit User'"
        :positive-text="!id ? 'Add' : 'Edit'"
        negative-text="Cancel"
        @positive-click="add"
        @negative-click="
            form.reset();
            emit('close');
        "
    >
        <n-input
            type="text"
            v-model:value="form.username"
            placeholder="Username"
            style="margin-top: 1rem"
            :status="form.errors.username ? 'error' : undefined"
        />
        <n-input
            type="password"
            v-model:value="form.password"
            placeholder="Password"
            style="margin-top: 0.5rem"
            :status="form.errors.password ? 'error' : undefined"
            v-if="!id"
        />
        <n-checkbox v-model:checked="form.is_admin" style="margin-top: 0.5rem"
            >Is Admin</n-checkbox
        >
        <n-p style="color: #e88080; margin-top: 0" v-if="form.errors.username"
            >{{ form.errors.username }} {{ form.errors.password }}</n-p
        >
    </n-modal>
</template>
