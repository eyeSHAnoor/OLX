import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { usePlanPermissions } from './usePlanPermissions'

export function useTheme() {
  const { hasPlanPermission } = usePlanPermissions()
  const page = usePage()
  const user = computed(() => page.props.auth?.user)

  const activePlan = computed(() => {
    if (hasPlanPermission('premium_batch')) return 'premium'
    if (hasPlanPermission('pro_batch')) return 'pro'
    return 'free'
  })

  const theme = computed(() => {
    const plans = {
      premium: {
        bg: 'bg-black',
        bgLight: 'bg-gray-700',
        bgNav: 'bg-gradient-to-r from-black/80 via-gray-900 to-black',
        text: 'text-white',
        textMuted: 'text-gray-400',
        textAccent: 'text-brand-orange',
        border: 'border-brand-orange/20',
        borderHover: 'hover:border-brand-orange/40',
        shadow: 'shadow-xl shadow-brand-orange/10',
        card: 'bg-gray-900/70 backdrop-blur-sm',
        cardBorder: 'border-brand-orange/10',
        button: 'bg-brand-orange hover:bg-brand-orange/80 text-black',
        buttonOutline: 'border-brand-orange/30 text-brand-orange hover:bg-brand-orange/10',
        input: 'bg-gray-800 border-brand-orange/20 text-white',
        badge: 'bg-brand-orange text-black',
        gradient: 'from-brand-orange via-yellow-500 to-brand-orange',
        hover: 'hover:bg-gray-800',
        icon: 'text-brand-orange',
        message: 'text-white',
      },
      pro: {
        bg: 'bg-brand-navy',
        bgLight: 'bg-brand-navy/70',
        bgNav: 'bg-gradient-to-r from-brand-navy/70 via-brand-navy/80 to-brand-navy',
        text: 'text-white',
        textMuted: 'text-blue-100/70',
        textAccent: 'text-blue-200',
        border: 'border-white/20',
        borderHover: 'hover:border-white/40',
        shadow: 'shadow-xl shadow-brand-navy/20',
        card: 'bg-brand-navy/70 backdrop-blur-sm',
        cardBorder: 'border-white/10',
        button: 'bg-brand-teal hover:bg-brand-teal/80 text-brand-blue',
        buttonOutline: 'border-white/30 text-white hover:bg-white/10',
        input: 'bg-white/10 border-white/20 text-white',
        badge: 'bg-white text-brand-navy',
        gradient: 'from-brand-navy via-blue-400 to-brand-navy',
        hover: 'hover:bg-white/5',
        icon: 'text-brand-teal',
        message: 'text-white',

      },
      free: {
        bg: 'bg-gray-50',
        bgLight: 'bg-gray-100',
        bgNav: 'bg-gradient-to-r from-brand-blue/15 via-brand-teal/15 to-brand-orange/15',
        text: 'text-gray-900',
        textMuted: 'text-gray-500',
        textAccent: 'text-brand-blue',
        border: 'border-gray-200',
        borderHover: 'hover:border-gray-300',
        shadow: 'shadow-sm',
        card: 'bg-white',
        cardBorder: 'border-gray-100',
        button: 'bg-brand-blue hover:bg-blue-700 text-white',
        buttonOutline: 'border-brand-blue/30 text-brand-blue hover:bg-brand-blue/10',
        input: 'bg-white border-gray-300 text-gray-900',
        badge: 'bg-gray-200 text-gray-700',
        gradient: 'from-brand-blue via-brand-teal to-brand-orange',
        hover: 'hover:bg-gray-50',
        icon: 'text-brand-teal',
        message: 'text-black',
      }
    }
    return plans[activePlan.value]
  })

  return { theme, activePlan }
}