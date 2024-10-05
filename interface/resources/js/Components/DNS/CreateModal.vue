<template>
    <n-modal
        :show="visible"
        preset="dialog"
        role="dialog"
        title="Create DNS Record"
        positive-text="Create"
        negative-text="Cancel"
        @positive-click="createRecord"
        @negative-click="
            form.reset();
            emit('close');
        "
    >
        <n-input-group style="margin-top: 1rem">
            <n-input
                type="text"
                v-model:value="form.name"
                placeholder="Name"
                style="width: 75%"
                :status="form.errors.name ? 'error' : undefined"
                :allow-input="noSideSpace"
                @input="form.errors.name = undefined"
            />
            <n-input-group-label>{{ `.${domain.name}` }}</n-input-group-label>
        </n-input-group>
        <n-p style="color: #e88080; margin-top: 0" v-if="form.errors.name">{{
            form.errors.name
        }}</n-p>
        <n-select
            v-model:value="form.type"
            style="margin-top: 1rem"
            placeholder="Type"
            :options="options"
        />
        <n-input
            type="text"
            v-model:value="form.value"
            placeholder="Value"
            style="margin-top: 1rem"
            :status="form.errors.value ? 'error' : undefined"
            @input="form.errors.value = undefined"
        />
        <n-p style="color: #e88080; margin-top: 0" v-if="form.errors.value">{{
            form.errors.value
        }}</n-p>
    </n-modal>
</template>

<script setup lang="ts">
import { Domain } from "@/types";

import { useForm } from "@inertiajs/vue3";
import {
    NModal,
    NInput,
    NInputGroup,
    NInputGroupLabel,
    NP,
    NSelect,
} from "naive-ui";

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
    name: "",
    type: "a",
    value: "",
});

const options = [
    {
        label: "A",
        value: "a",
    },
    {
        label: "AAAA",
        value: "aaaa",
    },
    {
        label: "CNAME",
        value: "cname",
    },
    {
        label: "NS",
        value: "ns",
    },
    {
        label: "MX",
        value: "mx",
    },
    {
        label: "TXT",
        value: "txt",
    },
    {
        label: "PTR",
        value: "ptr",
    },
];

const createRecord = () => {
    if (!form.name) {
        form.errors.name = "Name is required";
        return;
    }
    if (!form.value) {
        form.errors.value = "Value is required";
        return;
    }

    form.post(route("dns.store", props.domain.id), {
        preserveScroll: true,
        preserveState: false,
        onSuccess: () => {
            form.reset("name");
            form.reset("value");
            emit("close");
        },
    });
};

const noSideSpace = (value: string) => {
    return !/\s/g.test(value);
};
</script>
