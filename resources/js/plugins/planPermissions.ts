import { usePlanPermissions } from '@/composables/usePlanPermissions'

export default {
  install(app: any) {
    app.config.globalProperties.$hasPlanPermission = (permission: string, user?: any) => {
      const { hasPlanPermission } = usePlanPermissions(user)
      return hasPlanPermission(permission)
    }

    app.config.globalProperties.$hasAnyPlanPermission = (permissions: string[], user?: any) => {
      const { hasAnyPlanPermission } = usePlanPermissions(user)
      return hasAnyPlanPermission(permissions)
    }

    app.config.globalProperties.$hasAllPlanPermissions = (permissions: string[], user?: any) => {
      const { hasAllPlanPermissions } = usePlanPermissions(user)
      return hasAllPlanPermissions(permissions)
    }
  },
}