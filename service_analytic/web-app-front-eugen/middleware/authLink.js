export default async function (context) {
    if (context.route.query.is_admin == '1') {
        context.$auth.strategy.token.set(context.route.query.token);
        await context.$auth.fetchUser();
    }
}
