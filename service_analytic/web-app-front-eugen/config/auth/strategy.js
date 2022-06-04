import { LocalScheme } from '~auth/runtime';
import { get } from 'lodash';

export default class CustomScheme extends LocalScheme {
    // Override `fetchUser` method of `local` scheme
    async fetchUser(endpoint) {
        // Token is required but not available
        if (!this.check().valid) {
            return;
        }

        // User endpoint is disabled.
        if (!this.options.endpoints.user) {
            this.$auth.setUser({});
            return;
        }

        // Try to fetch user and then set
        return this.$auth
            .requestWith(this.name, endpoint, this.options.endpoints.user)
            .then(response => {
                console.log(
                    '🚀 ~ file: strategy.js ~ line 21 ~ CustomScheme ~ fetchUser ~ response',
                    response?.status
                );
                const user = get(response.data, this.options.user.property);
                // Transform the user object
                const customUser = {
                    ...user,
                    // fullName: user.firstName + ' ' + user.lastName,
                    // roles: ['user'],
                };

                // Set the custom user
                // The `customUser` object will be accessible through `this.$auth.user`
                // Like `this.$auth.user.fullName` or `this.$auth.user.roles`
                this.$auth.setUser(customUser);

                return response;
            })
            .catch(error => {
                this.$auth.callOnError(error, { method: 'fetchUser' });
            });
    }

    async logout(endpoint) {
        // Only connect to logout endpoint if it's configured
        if (this.options.endpoints.logout) {
            await this.$auth
                .requestWith(this.name, endpoint, this.options.endpoints.logout)
                .catch(err => {
                    console.log(
                        '🚀 ~ file: strategy.js ~ line 51 ~ CustomScheme ~ logout ~ err',
                        err
                    );
                    //
                });
        }

        // But reset regardless
        await this.$auth.reset();
        document.cookie.split(';').forEach(cookie => {
            const [nameCookie] = cookie.split('=');
            document.cookie = `${nameCookie}=;expires=Thu, 01 Jan 1970 00:00:00 GMT;`;
        });

        const exceptionLSKey = ['completedRoutes'];

        Object.keys(localStorage).forEach(key => {
            if (!exceptionLSKey.includes(key)) {
                localStorage.removeItem(key);
            }
        });
    }
}
