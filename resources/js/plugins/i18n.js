import { createI18n } from 'vue-i18n';

export default function setupI18n(locale, messages) {
    return createI18n({
        legacy: false,
        globalInjection: true,
        locale: locale || 'en',
        fallbackLocale: 'en',
        messages: {
            [locale]: messages ?? {},
        },
    });
}
