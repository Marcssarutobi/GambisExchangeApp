import { createRouter, createWebHistory } from "vue-router";

const routes = [
    {
        path:'/',
        component: ()=>import('../layout/contentWrapper.vue'),
        children:[
            {
                path: '',
                component: () => import('../pages/home.vue')
            }
        ]
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior(to, from, savedPosition) {
        if (to.hash) {
            return {
                el: to.hash,
                behavior: 'smooth',
                top: 0
            }
        } else {
            return { top: 0 }
        }
    }
})

export default router;