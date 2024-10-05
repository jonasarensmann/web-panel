<template>
    <n-modal
        :show="visible"
        preset="dialog"
        role="dialog"
        title="Update DNS Record"
        positive-text="Update"
        negative-text="Cancel"
        @positive-click="createRecord"
        @negative-click="$emit('close')"
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
            <n-input-group-label>
                <s v-if="form.name.endsWith('.')">
                    {{ `.${domain.name}` }}
                </s>
                <template v-else>
                    {{ `.${domain.name}` }}
                </template>
            </n-input-group-label>
        </n-input-group>
        <template v-if="form.name.endsWith('.')">
            <n-alert type="info">
                The name ends with a period, the domain name will not be
                appended.
            </n-alert>
        </template>
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
    NAlert,
} from "naive-ui";
import { watch } from "vue";

interface Record {
    name: string;
    type: string;
    value: string;
}

const props = defineProps({
    visible: {
        type: Boolean,
        required: true,
    },
    domain: {
        type: Object as () => Domain,
        required: true,
    },
    record: {
        type: Object as () => Record | null,
        default: null,
    },
});

const emit = defineEmits(["close"]);

const form = useForm({
    name: "",
    type: "a",
    value: "",
});

watch(
    () => props.record,
    (record) => {
        if (record) {
            form.name = record.name;
            form.type = record.type;
            form.value = record.value;
        }
    }
);

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

    form.transform((data) => {
        data.type = data.type.toLowerCase();
        return data;
    }).patch(route("dns.update", props.domain.id), {
        preserveScroll: true,
        preserveState: false,
        onSuccess: () => {
            emit("close");
        },
    });
};

const noSideSpace = (value: string) => {
    return !/\s/g.test(value);
};
</script>
