<template>
    <!-- Start Sidebar -->
    <aside id="app-menu"
        class="w-sidenav min-w-sidenav bg-white shadow overflow-y-auto fixed inset-y-0 start-0 z-60 hidden -translate-x-full transform transition-all duration-200 lg:bottom-0 lg:end-auto lg:z-30 lg:block lg:translate-x-0 rtl:translate-x-full rtl:lg:translate-x-0 print:hidden">

        <div class="flex flex-col h-full">
            <!-- Sidenav Logo -->
            <div class="sticky top-0 flex h-topbar items-center justify-center px-6">
                <a href="index.html">
                    <img src="/assets/images/logoG.png" alt="logo" class="flex h-12 w-auto">
                </a>
            </div>

            <div class="p-4 h-[calc(100%-theme('spacing.topbar'))] flex-grow" data-simplebar>
                <!-- Menu -->
                <ul class="admin-menu hs-accordion-group flex w-full flex-col gap-1">
                    <li class="px-3 py-2 text-xs uppercase font-medium text-default-500">Menu</li>


                    <li class="menu-item">
                        <RouterLink
                            class="group flex items-center gap-x-3.5 rounded-md px-3 py-2 text-sm font-medium text-default-600 transition-all hover:bg-primary/5"
                            exact-active-class="active" to="/">
                            <i class="fa-regular fa-home size-5"></i>
                            Dashbord
                        </RouterLink>
                    </li>

                    <li class="menu-item">
                        <RouterLink
                            class="group flex items-center gap-x-3.5 rounded-md px-3 py-2 text-sm font-medium text-default-600 transition-all hover:bg-primary/5"
                            exact-active-class="active" to="/customer">
                            <i class="fa-regular fa-user-tie size-5"></i>
                            Customers
                        </RouterLink>
                    </li>

                    <li class="menu-item">
                        <RouterLink
                            class="group flex items-center gap-x-3.5 rounded-md px-3 py-2 text-sm font-medium text-default-600 transition-all hover:bg-primary/5"
                            exact-active-class="active" to="/account">
                            <i class="fa-light fa-file-invoice size-5"></i>
                            Accounts
                        </RouterLink>
                    </li>

                    <li class="menu-item">
                        <RouterLink
                            class="group flex items-center gap-x-3.5 rounded-md px-3 py-2 text-sm font-medium text-default-600 transition-all hover:bg-primary/5"
                            exact-active-class="active" to="/exchange">
                            <i class="fa-regular fa-arrow-right-arrow-left size-5"></i>
                            Exchanges
                        </RouterLink>
                    </li>

                    <li class="px-3 py-2 text-xs uppercase font-medium text-default-500">Currency settings</li>

                    <!-- <li class="menu-item">
                        <RouterLink to="/currencypurchases"
                            class="group flex items-center gap-x-3.5 rounded-md px-3 py-2 text-sm font-medium text-default-600 transition-all hover:bg-primary/5"
                            exact-active-class="active">
                            <i class="i-lucide-clipboard size-5"></i>
                            <span class="menu-text"> Currency Purchases </span>
                        </RouterLink>
                    </li> -->

                    <li class="menu-item">
                        <RouterLink to="/currency"
                            class="group flex items-center gap-x-3.5 rounded-md px-3 py-2 text-sm font-medium text-default-600 transition-all hover:bg-primary/5"
                            exact-active-class="active">
                            <i class="i-lucide-clipboard size-5"></i>
                            <span class="menu-text"> Currency </span>
                        </RouterLink>
                    </li>
                    <li class="menu-item">
                        <RouterLink to="/rate"
                            class="group flex items-center gap-x-3.5 rounded-md px-3 py-2 text-sm font-medium text-default-600 transition-all hover:bg-primary/5"
                            exact-active-class="active">
                            <i class="i-lucide-clipboard size-5"></i>
                            <span class="menu-text"> Exchanges Rate </span>
                        </RouterLink>
                    </li>

                    <li class="px-3 py-2 text-xs uppercase font-medium text-default-500">Admin Setting</li>

                    <li class="menu-item">
                        <RouterLink to="/user"
                            class="group flex items-center gap-x-3.5 rounded-md px-3 py-2 text-sm font-medium text-default-600 transition-all hover:bg-primary/5"
                            exact-active-class="active">
                            <i class="i-lucide-palette size-5"></i>
                            <span class="menu-text"> Users </span>
                        </RouterLink>
                    </li>

                </ul>
            </div>

        </div>
    </aside>
    <!-- End Sidebar -->
</template>
<script setup>
import { RouterLink } from 'vue-router';


</script>
<style scoped>
.size-5 {
    font-size: 20px;
}
</style>