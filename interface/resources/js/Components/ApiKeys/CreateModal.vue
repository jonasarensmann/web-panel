<template>
    <n-modal :show="visible" preset="dialog" role="dialog" title="Create API Key" positive-text="Create"
        negative-text="Cancel" @positive-click="createToken" @negative-click="form.reset(); emit('close')">
        <n-input type="text" v-model:value="form.name" placeholder="Name" style="margin-top: 1rem;"
            :status="form.errors.name ? 'error' : undefined" />
        <n-p style="color: #e88080; margin-top: 0;" v-if="form.errors.name">{{
            form.errors.name }}</n-p>
        <n-date-picker style="margin-top: 1rem" v-model:value="form.expires_at" type="datetime" clearable placeholder="Expiration date" />
    </n-modal>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';

import { NModal, NInput, NDatePicker, NP } from 'naive-ui';

defineProps({
    visible: {
        type: Boolean,
        required: true
    },
});

const emit = defineEmits(['close'])

const form = useForm({
    name: '',
    expires_at: null,
});

const createToken = () => {
    if (!form.name) {
        form.errors.name = 'Name is required';
        return;
    }

    form.transform((date) => ({
        ...date,
        expires_at: date.expires_at ? new Date(date.expires_at) : null
    })
    ).post(route('tokens.store'), {
        preserveScroll: true,
        preserveState: false,
        onFinish: () => {
            form.reset('name');
        },
        onSuccess: () => {
            emit('close');
        }
    });
};

</script>
