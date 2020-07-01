Nova.booting((Vue, router, store) => {
  router.addRoutes([
    {
      name: 'wiki',
      path: '/wiki',
      component: require('./components/Tool'),
    },
  ])
})
