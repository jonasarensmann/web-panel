<script setup lang="ts">
import { NConfigProvider, darkTheme } from 'naive-ui';
</script>

<template>
    <n-config-provider :theme="darkTheme">
        <slot />
    </n-config-provider>
</template>
