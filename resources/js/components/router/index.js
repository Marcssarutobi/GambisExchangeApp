import { createRouter, createWebHistory } from "vue-router";
import axiosInstance from "../plugins/axios";

const routes = [
    {
        path: '/',
        component: () => import('../layout/contentWrapper.vue'),
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                component: () => import('../pages/home.vue')
            },
            {
                path: 'customer',
                component: () => import('../pages/customer.vue')
            },
            {
                path: 'account',
                component: () => import('../pages/accounts.vue')
            },
            {
                path: 'exchange',
                component: () => import('../pages/exchange.vue')
            },
            {
                path: 'currency',
                component: () => import('../pages/currency.vue')
            },
            {
                path: 'rate',
                component: () => import('../pages/rate.vue')
            },
            {
                path: 'user',
                component: () => import('../pages/user.vue')
            },
            {
                path: 'profile',
                component: () => import('../pages/profile.vue')
            }
        ]
    },
    {
        path: '/login',
        component: () => import('../pages/login.vue')
    },
    {
        path: '/forgot-password',
        component: () => import('../pages/forgotPassword.vue')
    },
    {
        path: '/verify-code',
        component: () => import('../pages/verifyCode.vue')
    },
    {
        path: '/change-password',
        component: () => import('../pages/changePassword.vue')
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

export async function isAuthenticated() {
    try {

        const res = await axiosInstance.get('/user', {
            headers: {
                Authorization: `Bearer ${localStorage.getItem("token")}`,
            },
        })
        if (res.status === 200) {
            return res.data.user
        }
    } catch (error) {
        console.error(
            "Erreur lors de la vérification de l'authentification :",
            error
        );
        return null;
    }
}



router.beforeEach(async (to, from, next) => {
    if (to.matched.some((record) => record.meta.requiresAuth)) {
        try {
            const auth = await isAuthenticated()
            const token = localStorage.getItem("token");
            if (auth && token) {
                //Vérifier si le statut de l'utilisateur est égal à inactive
                if (auth.status === 'inactive') {
                    localStorage.removeItem("token");
                    localStorage.setItem('redirectAfterLogin', to.fullPath);
                    return next('/login');
                }
                next()
            } else {
                localStorage.setItem('redirectAfterLogin', to.fullPath); // <- ici
                next('/login')
            }
        } catch (error) {
            console.error(
                "Erreur lors de la vérification de l'authentification :",
                error
            );
            next("/login");
        }
    } else {
        // Rediriger les utilisateurs authentifiés accédant à la page de connexion
        if (to.path === '/login') {
            const auth = await isAuthenticated()
            const token = localStorage.getItem("token");
            if (auth && token) {
                next('/')
            } else {
                next()
            }
        } else {
            next();
        }
    }
})

export default router;