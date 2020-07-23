
let TagIndex = require('./views/Index.vue').default
let TagCreate = require('./views/Create.vue').default

const TagsRoutes = [
    { path: '/tag', component: TagIndex, name: 'TagIndex' },
    { path: '/tag/create', component: TagCreate, name: 'TagCreate' }
]

export default TagsRoutes
