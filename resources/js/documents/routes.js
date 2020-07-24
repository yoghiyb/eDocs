
let DocumentIndex = require('./views/Index.vue').default
let DocumentUpload = require('./views/Upload.vue').default

const DocumentsRoutes = [
    { path: '/documents', component: DocumentIndex, name: 'DocumentIndex' },
    { path: '/document/upload', component: DocumentUpload, name: 'DocumentUpload' },
]

export default DocumentsRoutes
