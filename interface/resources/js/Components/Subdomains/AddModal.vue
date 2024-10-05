<template>
    <n-modal
        :show="visible"
        preset="dialog"
        role="dialog"
        title="Add Subdomain"
        positive-text="Add"
        negative-text="Cancel"
        @positive-click="addSubdomain"
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
    </n-modal>
</template>

<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";
import { NModal, NInput, NSelect, NP, NInputGroup } from "naive-ui";
import { SelectMixedOption } from "naive-ui/es/select/src/interface";

const props = defineProps({
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
    domain: undefined,
});

let added = false;

const addSubdomain = () => {
    if (!form.domain) {
        form.errors.domain = "Domain is required";
        return;
    }
    if (!form.name) {
        form.errors.name = "Name is required";
        return;
    }

    if (added) {
        form.errors.domain = "Subdomain is already being added";
        return;
    }

    added = true;

    const domainFromProps = props.domains.find(
        (domain) => domain.value === form.domain
    );

    form.transform((data) => {
        return {
            ...data,
            domain:
                data.domain !== undefined
                    ? (domainFromProps!.label! as string).slice(1)
                    : undefined,
        };
    }).post(route("subdomains.store"), {
        preserveScroll: true,
        preserveState: false,
        onStart: () => {
            /* setTimeout(() => {
                form.reset();
                location.reload();
            }, 3000); */
        },
    });
};
</script>
