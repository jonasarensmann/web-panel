<script setup lang="ts">
import {
    NConfigProvider,
    NLayout,
    NLayoutHeader,
    NLayoutContent,
    darkTheme,
} from "naive-ui";
import Navigation from "@/Components/Navigation.vue";
</script>

<template>
    <n-config-provider :theme="darkTheme">
        <n-layout>
            <n-layout-header bordered class="p-4">
                <Navigation>
                    <template #breadcrumb>
                        <slot name="breadcrumb" />
                    </template>

                    <template #page-title>
                        <slot name="page-title" />
                    </template>
                </Navigation>
            </n-layout-header>

            <n-layout-content :native-scrollbar="false" content-class="p-6">
                <slot />
            </n-layout-content>
        </n-layout>
    </n-config-provider>
</template>

<style>
:root {
    background-color: rgb(16, 16, 20);
}
</style>
