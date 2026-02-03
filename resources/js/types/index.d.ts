import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export type { PageProps as InertiaPageProps } from '@inertiajs/core';

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: string;
    isActive?: boolean;
    visible?: boolean;
    children?: any;
    badge?: number;
    meta?: any;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export type BreadcrumbItemType = BreadcrumbItem;

export interface SharedData extends PageProps {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
    settings: any;
    notifications_count: number;
    flash: {
        success?: string;
        error?: string;
    };
}

export type PaginatedData<T> = {
    data: T[];
    current_page: number;
    from: number;
    last_page: number;
    path: string;
    per_page: number;
    to: number;
    total: number;
    links: {
        url?: string;
        label: string;
        active: boolean;
    }[];
};

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface PaginationData {
    from: number;
    to: number;
    total: number;
    links: PaginationLink[];
    per_page: number | string;
}

export interface TableFilterItem {
    value: number | string;
    label: string;
    icon?: string;
}
