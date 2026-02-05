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

export interface BrandData {
    id: number;
    name: string;
    created_at: string;
    updated_at: string;
    categories?: CategoryData[];
}

export interface CategoryData {
    id: number;
    name: string;
    slug: string;
    parent_id: number | null;
    position: number;
    children_recursive?: CategoryData[];
}

declare global {
    namespace App.Data {
        interface AdData {
            id: number;
            user_id: number;
            category_id: number;
            brand_id: number;
            ad_title: string;
            description: string;
            price: number;
            location: string;
            seller_name: string;
            seller_phone: string;
            created_at: string;
            updated_at: string;
            category?: CategoryData;
            brand?: BrandData;
            images?: AdImageData[];
            primary_image?: AdImageData;
            images_count?: number;
        }

        interface AdImageData {
            id: number;
            ad_id: number;
            path: string;
            is_primary: boolean;
            created_at: string;
            updated_at: string;
        }
    }
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
