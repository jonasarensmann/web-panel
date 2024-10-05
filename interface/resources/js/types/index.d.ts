export interface User {
    id: number;
    user: string;
}

export interface CaddyconfigEntry {
        reverse_proxy?: string;
        file_server?: string | boolean;
        root?: [string, string];
        encode?: [string];
        php_fastcgi?: string;
        respond?: string;
}

export interface Caddyconfig {
    // subdomains
    [key: string]: {
        [key: string]: CaddyconfigEntry;
    };
    // root
    [key: string]: CaddyconfigEntry;
}

export interface DNS {
    $origin: string;
    $ttl: number;
    soa: {
        mname: string;
        rname: string;
        serial: number;
        refresh: number;
        retry: number;
        expire: number;
        minimum: number;
    };
    ns: [{
        host: string;
    }];
    mx: [{
        preference: number;
        host: string;
    }];
    a: [{
        id: any;
        name: string;
        ip: string;
    }];
    aaaa: [{
        name: string;
        ip: string;
    }];
    cname: [{
        name: string;
        alias: string;
    }];
    txt: [{
        name: string;
        txt: string;
    }];
    srv: [{
        name: string;
        target: string;
        port: number;
        priority: number;
        weight: number;
    }];
    ptr: [{
        name: string;
        host: string;
    }];
}

export interface Domain {
    id: number;
    name: string;
    locked: boolean;
    dns: DNS;
    caddyconfig: Caddyconfig;
}

export interface Subdomain {
    id: number;
    domain_id: number;
    name: string;
}

export interface Token {
    id: number;
    name: string;
    token: string;
    abilities: string;
    last_used_at: string;
    expires_at: string;
    created_at: string;
    updated_at: string;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>
> = T & {
    auth: {
        user: User;
    };
};

export interface Message {
    id: number;
    title: string;
    content: string;
    type: string;
    read: boolean;
    created_at: string;
    updated_at: string;
}

export interface Permissions {
    view_logs: boolean;
}

export interface User {
    id: number;
    username: string;
    is_admin: number;
    permissions: Permissions;
    created_at: string;
    updated_at: string;
}

export interface File {
    name: string,
    size: number,
    type: "file" | "directory",
}
