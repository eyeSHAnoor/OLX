import type { Updater } from '@tanstack/vue-table';
import { useUrlSearchParams } from '@vueuse/core';
import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';
import type { Ref } from 'vue';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function valueUpdater<T extends Updater<any>>(updaterOrValue: T, ref: Ref) {
    ref.value = typeof updaterOrValue === 'function' ? updaterOrValue(ref.value) : updaterOrValue;
}

export function getQueryParam(name?: string): string | Record<string, string> {
    const params = useUrlSearchParams('history');

    if (name) {
        const value = params[name];
        return typeof value === 'string' ? value : '';
    }

    // Convert to normal object and filter out undefined values
    const result: Record<string, string> = {};
    for (const [key, value] of Object.entries(params)) {
        if (typeof value === 'string') {
            result[key] = value;
        }
    }
    return result;
}

export const languages = [
    { value: 'en', label: 'English', icon: 'twemoji:flag-us-outlying-islands' },
    { value: 'hr', label: 'Croatian', icon: 'twemoji:flag-croatia' },
];
