
let UserIndex = require('./views/Index.vue').default
let UserCreate = require('./views/Create.vue').default

const UsersRoutes = [
    { path: '/user', component: UserIndex, name: 'UserIndex' },
    { path: '/user/create', component: UserCreate, name: 'UserCreate' },
]

export default UsersRoutes
