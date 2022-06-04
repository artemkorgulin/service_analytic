Nova.booting((Vue, router, store) => {
  router.addRoutes([
    {
      name: 'user-statistics',
      path: '/user-statistics',
      component: require('./components/Tool'),
    },
  ])
})
