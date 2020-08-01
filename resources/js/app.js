/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

import Swal from 'sweetalert2'
window.Swal = Swal;

import Multiselect from 'vue-multiselect'

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i);
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default));

Vue.component('example-component', require('./components/ExampleComponent.vue').default);
Vue.component('data-viewer', require('./components/DataViewer.vue').default);
Vue.component('multiselect', Multiselect);
Vue.component('comment', require('./components/CommentComponent.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

import VueProgressBar from 'vue-progressbar'

Vue.use(VueProgressBar, {
    color: 'rgb(143, 255, 199)',
    failedColor: 'red',
    height: '3px'
})

import VueRouter from 'vue-router'
import Routes from './routes'
import Axios from 'axios';
import { reject } from 'lodash';
Vue.use(VueRouter)

const router = new VueRouter({
    mode: 'history',
    routes: Routes, // short for `routes: routes`
    linkActiveClass: 'active'
})


const app = new Vue({
    el: '#app',
    router,
    data: {
        authUser: ''
    },
    async created() {
        window.addEventListener('load', function () {
            // Fetch all the forms we want to apply custom Bootstrap validation styles to
            var forms = document.getElementsByClassName('needs-validation');
            // Loop over them and prevent submission
            var validation = Array.prototype.filter.call(forms, function (form) {
                form.addEventListener('submit', function (event) {
                    if (form.checkValidity() === false) {
                        event.preventDefault();
                        event.stopPropagation();
                        console.log('Bootstrap will handle incomplete form fields');
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        }, false);
        await this.getAuthUser()
    },
    mounted() {

    },
    methods: {
        async getAuthUser() {
            let endpoint = `${BASE_URL}/user`
            axios.get(endpoint)
                .then(response => {
                    if (response.status === 200) {
                        this.authUser = response.data
                    }
                })
                .catch(error => console.log(error));
        },
    },
});
