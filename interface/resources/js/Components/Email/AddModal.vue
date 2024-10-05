<template>
    <n-modal
        :show="visible"
        preset="dialog"
        role="dialog"
        title="Add Email"
        positive-text="Add"
        negative-text="Cancel"
        @positive-click="addEmail"
        @negative-click="
            form.reset();
            emit('close');
        "
    >
        <n-input-group style="margin-top: 1rem">
            <n-input
                style="width: 40%"
                type="text"
                v-model:value="form.name"
                placeholder="Name"
                :status="form.errors.name ? 'error' : undefined"
            />
            <n-input style="width: 10%" type="text" value="@" disabled />
            <n-select
                style="width: 60%"
                v-model:value="form.domain"
                :options="domains"
                filterable
                placeholder="Select Domain"
                :status="form.errors.domain ? 'error' : undefined"
            />
        </n-input-group>
        <n-p style="color: #e88080; margin-top: 0" v-if="form.errors.domain">{{
            form.errors.domain
        }}</n-p>
        <n-input
            style="margin-top: 0.5rem"
            type="password"
            v-model:value="form.password"
            placeholder="Password"
            :status="form.errors.password ? 'error' : undefined"
        />
    </n-modal>
</template>

<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";
import { NModal, NInput, NSelect, NP, NInputGroup } from "naive-ui";
import { SelectMixedOption } from "naive-ui/es/select/src/interface";

defineProps({
    visible: {
        type: Boolean,
        required: true,
    },
    domains: {
        type: Array as () => SelectMixedOption[],
        required: true,
    },
});

const emit = defineEmits(["close"]);

const form = useForm({
    name: "",
    password: "",
    domain: undefined,
});

const addEmail = () => {
    if (!form.domain) {
        form.errors.domain = "Domain is required";
        return;
    }
    if (!form.name) {
        form.errors.name = "Name is required";
        return;
    }

    form.post(route("emails.store"), {
        preserveScroll: true,
        preserveState: false,
    });
};
</script>
