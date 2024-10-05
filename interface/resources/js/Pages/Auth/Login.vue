<script setup lang="ts">
import GuestLayout from "@/Layouts/GuestLayout.vue";

import { Head, useForm } from "@inertiajs/vue3";
import {
    NCard,
    NInput,
    NFormItem,
    NForm,
    NFlex,
    NButton,
    NCheckbox,
} from "naive-ui";

const form = useForm({
    username: "",
    password: "",
    remember: false,
});

const submit = () => {
    form.post(route("login"), {
        onFinish: () => {
            form.reset("password");
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <n-flex justify="center" align="center" class="h-screen">
            <n-card title="Log in" class="w-full md:w-2/4 lg:w-1/4">
                <n-form>
                    <n-form-item
                        label="Username"
                        path="username"
                        required
                        :validation-status="
                            form.errors.username ? 'error' : undefined
                        "
                        :feedback="form.errors.username"
                    >
                        <n-input v-model:value="form.username" type="text" />
                    </n-form-item>
                    <n-form-item
                        label="Password"
                        path="password"
                        required
                        :validation-status="
                            form.errors.password ? 'error' : undefined
                        "
                        :feedback="form.errors.password"
                    >
                        <n-input
                            v-model:value="form.password"
                            type="password"
                        />
                    </n-form-item>
                    <n-checkbox v-model:checked="form.remember" />
                    <span class="ml-2">Remember me</span>
                    <n-form-item>
                        <n-button type="primary" @click="submit"
                            >Log in</n-button
                        >
                    </n-form-item>
                </n-form>
            </n-card>
        </n-flex>
    </GuestLayout>
</template>
