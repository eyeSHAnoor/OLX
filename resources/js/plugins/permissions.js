import usePermissions from '../composables/usePermissions';

export default {
    install(app) {
        const permissions = usePermissions();
        app.config.globalProperties.hasRole = permissions.hasRole;
        app.config.globalProperties.hasPermissions = permissions.hasPermissions;
    },
};
