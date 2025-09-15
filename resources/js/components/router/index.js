import { createRouter, createWebHistory } from "vue-router";

const routes = [
    {
        path:'/',
        component: ()=>import('../layout/contentWrapper.vue'),
        children:[
            {
                path: '',
                component: () => import('../pages/home.vue')
            },
            {
                path: 'customer',
                component: () => import('../pages/customer.vue')
            },
            {
                path:'account',
                component: () => import('../pages/accounts.vue')
            },
            {
                path:'exchange',
                component: ()=>import('../pages/exchange.vue')
            },
            {
                path:'currency',
                component: ()=>import('../pages/currency.vue')
            },
            {
                path:'rate',
                component: ()=>import('../pages/rate.vue')
            },
            {
                path:'user',
                component: ()=>import('../pages/user.vue')
            }
        ]
    },
    {
        path: '/login',
        component: () => import('../pages/login.vue')
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