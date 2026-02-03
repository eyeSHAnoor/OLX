import usePermissions, { hasPermissions } from '../Composables/usePermissions';

export default {
    install(app) {
        const permissions = usePermissions()
        app.config.globalProperties.hasRole = permissions.hasRole
        app.config.globalProperties.hasPermissions = permissions.hasPermissions
    }
}
