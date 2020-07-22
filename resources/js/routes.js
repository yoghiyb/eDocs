/**
 * Import Route Aplikasi
 */
import ProfilesRoutes from './profiles/routes'
const routes = []

/**
 * Default case Pages
 */

let notFoundPage = { path: '*', component: require('./components/NotFound.vue').default }

/**
 * Push route aplikasi ke main route
 * (...AppRoute) untuk ekstrak array dari route aplikasi
 */
routes.push(
    ...ProfilesRoutes,


    notFoundPage,
)

/**
 * Export Main Route
 */

export default routes
