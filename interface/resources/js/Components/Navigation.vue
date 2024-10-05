<template>
    <n-page-header>
        <template #title>
            <n-breadcrumb>
                <slot name="breadcrumb" />
            </n-breadcrumb>
        </template>
        <template #header>
            <n-flex align="center">
                <slot name="page-title" />

                <div style="margin-left: auto">
                    <n-dropdown :options="options" @select="handleSelect">
                        <n-button>User</n-button>
                    </n-dropdown>
                </div>
            </n-flex>
        </template>

        <n-menu
            :options="menuOptions"
            v-model:value="activeKey"
            mode="horizontal"
            responsive
        />
    </n-page-header>
</template>

<script setup lang="ts">
import { Component, Ref, h, ref } from "vue";
import {
    NMenu,
    NPageHeader,
    NBreadcrumb,
    NIcon,
    NBadge,
    NFlex,
    NDropdown,
    NButton,
} from "naive-ui";
import type { MenuOption } from "naive-ui";

import { Link, router } from "@inertiajs/vue3";

import {
    HomeOutlined,
    KeyOutlined,
    PersonOutlined,
    LanguageOutlined,
    DomainOutlined,
    DnsOutlined,
    SettingsApplicationsOutlined,
    MonitorOutlined,
    FolderOutlined,
    MedicalInformationOutlined,
    EmailOutlined,
    CommentBankOutlined,
    UpdateOutlined,
    AdminPanelSettingsOutlined,
    ContactPageOutlined,
    MessageOutlined,
    LogOutOutlined,
} from "@vicons/material";
import axios from "axios";

const activeKey = ref("home");

function renderIcon(icon: Component) {
    return () => h(NIcon, null, { default: () => h(icon) });
}

function renderLink(title: string, to: string) {
    return () => h(Link, { href: route(to) }, { default: () => title });
}

const showUpdateBadge = ref(false);
const messageCount = ref(sessionStorage.getItem("messages") || 0);

const menuOptions: Ref<MenuOption[]> = ref([
    {
        key: "messages",
        icon: renderIcon(() =>
            h(
                NBadge,
                { value: messageCount.value },
                { default: () => h(MessageOutlined) }
            )
        ),
        label: renderLink("", "messages.index"),
    },
    {
        label: renderLink("Dashboard", "dashboard"),
        key: "dashboard",
        icon: renderIcon(() => h(HomeOutlined)),
    },
    {
        label: "Sites",
        key: "user",
        icon: renderIcon(() => h(ContactPageOutlined)),
        children: [
            {
                label: renderLink("Domains", "domains.index"),
                key: "domains",
                icon: renderIcon(() => h(LanguageOutlined)),
            },
            {
                label: renderLink("Subdomains", "subdomains.index"),
                key: "subdomains",
                icon: renderIcon(() => h(DomainOutlined)),
            },
            {
                label: renderLink("DNS Management", "dns.index"),
                key: "dns",
                icon: renderIcon(() => h(DnsOutlined)),
            },
            {
                label: renderLink("Email Management", "emails.index"),
                key: "emails",
                icon: renderIcon(() => h(EmailOutlined)),
            },
        ],
    },
    {
        label: "Miscellaneous",
        key: "misc",
        icon: renderIcon(() => h(FolderOutlined)),
        children: [
            {
                label: renderLink("API Keys", "tokens.index"),
                key: "api-keys",
                icon: renderIcon(() => h(KeyOutlined)),
            },
            {
                label: renderLink("File Manager", "files.index"),
                key: "file-manager",
                icon: renderIcon(() => h(FolderOutlined)),
            },
            {
                label: renderLink("Terminal", "terminal.index"),
                key: "terminal",
                icon: renderIcon(() => h(CommentBankOutlined)),
            },
        ],
    },
    {
        label: "Admin",
        key: "admin",
        icon: renderIcon(() =>
            h(
                NBadge,
                { dot: true, show: showUpdateBadge.value },
                { default: () => h(AdminPanelSettingsOutlined) }
            )
        ),
        show: sessionStorage.getItem("isadmin") === "true" ? true : false,
        children: [
            {
                label: renderLink("Users", "users.index"),
                key: "users",
                icon: renderIcon(() => h(PersonOutlined)),
            },
            {
                label: renderLink("Updates", "updates.index"),
                key: "updates",
                icon: renderIcon(() =>
                    h(
                        NBadge,
                        { dot: true, show: showUpdateBadge.value },
                        { default: () => h(UpdateOutlined) }
                    )
                ),
            },
        ],
    },
    {
        label: "Information",
        key: "info",
        icon: renderIcon(() => h(MedicalInformationOutlined)),
        children: [
            {
                label: renderLink("System Information", "systeminfo.index"),
                key: "system-information",
                icon: renderIcon(() => h(MedicalInformationOutlined)),
            },
            {
                label: renderLink("Log Files", "logs.index"),
                key: "log-files",
                icon: renderIcon(() => h(FolderOutlined)),
            },
        ],
    },
]);

const options = [
    {
        label: "User Settings",
        key: "user-settings",
        icon: renderIcon(PersonOutlined),
    },
    {
        label: "Logout",
        key: "logout",
        icon: renderIcon(LogOutOutlined),
    },
];

const handleSelect = (key: string) => {
    if (key === "logout") {
        axios.post(route("logout")).then(() => {
            router.get(route("login"));
        });
    }
};

const checkAdmin = () => {
    if (sessionStorage.getItem("isadmin") === "true") {
        menuOptions.value.forEach((option) => {
            if (option.key === "admin") {
                option.show = true;
            }
        });
        return;
    }

    axios.get(route("admin.check")).then((response) => {
        if (response.data.isadmin) {
            menuOptions.value.forEach((option) => {
                if (option.key === "admin") {
                    option.show = true;
                    sessionStorage.setItem("isadmin", "true");
                }
            });
        }
    });
};
checkAdmin();

const checkMessages = () => {
    axios.get(route("messages.check")).then((response) => {
        messageCount.value = response.data;
        sessionStorage.setItem("messages", response.data);
    });
};
checkMessages();

const checkUpdate = () => {
    if (sessionStorage.getItem("update") === "true") {
        showUpdateBadge.value = true;
        return;
    }

    axios.get(route("updates.check")).then((response) => {
        if (response.data && response.data !== "false") {
            showUpdateBadge.value = true;
            sessionStorage.setItem("update", "true");
        }
    });
};

if (sessionStorage.getItem("isadmin") === "true") checkUpdate();
</script>
