
let DocumentIndex = require('./views/Index.vue').default
let DocumentUpload = require('./views/Upload.vue').default
let DocumentEdit = require('./views/Edit.vue').default
let DocumentDetail = require('./views/Detail.vue').default

const DocumentsRoutes = [
    { path: '/documents', component: DocumentIndex, name: 'DocumentIndex' },
    { path: '/document/upload', component: DocumentUpload, name: 'DocumentUpload' },
    { path: '/document/:id/detail', component: DocumentDetail, name: 'DocumentDetail' },
    { path: '/document/:id/edit', component: DocumentEdit, name: 'DocumentEdit' }
]

export default DocumentsRoutes
