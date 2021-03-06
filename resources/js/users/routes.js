
let UserIndex = require('./views/Index.vue').default
let UserCreate = require('./views/Create.vue').default
let UserEdit = require('./views/Edit.vue').default
let UserDetail = require('./views/Detail.vue').default

const UsersRoutes = [
    { path: '/user', component: UserIndex, name: 'UserIndex' },
    { path: '/user/create', component: UserCreate, name: 'UserCreate' },
    { path: '/user/:id/detail', component: UserDetail, name: 'UserDetail' },
    { path: '/user/:id/edit', component: UserEdit, name: 'UserEdit' },
]

export default UsersRoutes
