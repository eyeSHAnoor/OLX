import { onBeforeUnmount, onMounted } from 'vue';
import { updateTheme } from './useAppearance';

type Appearance = 'light' | 'dark' | 'system';

export function useForceTheme(forcedTheme: Appearance) {
    let previousAppearance: Appearance | null = null;

    onMounted(() => {
        // Save user's current preference
        previousAppearance = (localStorage.getItem('appearance') as Appearance) || 'system';

        // Apply forced theme
        updateTheme(forcedTheme);
    });

    onBeforeUnmount(() => {
        // Restore previous theme
        if (previousAppearance) {
            updateTheme(previousAppearance);
        }
    });
}
