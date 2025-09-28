<template>
    <!-- Topbar Start -->
    <header class="app-header sticky top-0 z-50 p-4 pb-0 bg-white/5 backdrop-blur-sm">
        <div class="min-h-topbar flex items-center bg-white rounded-md shadow">
            <div class="px-6 w-full flex items-center justify-between gap-4">
                <div class="flex items-center gap-5">
                    <!-- Sidenav Menu Toggle Button -->
                    <button @click="toggleSidebar"
                        class="flex items-center text-default-500 rounded-full cursor-pointer p-2 bg-white border border-default-200 hover:bg-primary/15 hover:text-primary hover:border-primary/5 transition-all"
                        aria-label="Toggle navigation">
                        <i class="i-lucide-align-left text-2xl"></i>
                    </button>

                    <!-- Topbar Brand Logo -->
                    <a href="index.html" class="md:hidden flex">
                        <img src="/assets/images/logo-sm.png" class="h-5" alt="Small logo">
                    </a>

                    <!-- Topbar Search -->
                    <!-- <div class="md:flex hidden items-center relative">
                        <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                            <i class="i-tabler-search text-base"></i>
                        </div>
                        <input type="search"
                            class="form-input px-10 rounded-lg  bg-default-500/10 border-transparent focus:border-transparent w-80"
                            placeholder="Search...">
                        <button type="button" class="absolute inset-y-0 end-0 flex items-center pe-3">
                            <i class="i-tabler-microphone text-base hover:text-black"></i>
                        </button>
                    </div> -->
                </div>

                <div class="flex items-center gap-5">

                    <!-- Notification Dropdown Button -->
                    <!-- <div class="hs-dropdown relative inline-flex [--placement:bottom-right]">
                        <button type="button"
                            class="hs-dropdown-toggle inline-flex items-center p-2 rounded-full bg-white border border-default-200 hover:bg-primary/15 hover:text-primary transition-all">
                            <i class="i-lucide-bell text-2xl"></i>
                        </button>

                        <div
                            class="hs-dropdown-menu duration mt-2 w-full max-w-sm rounded-lg border border-default-200 bg-white opacity-0 shadow-md transition-[opacity,margin] hs-dropdown-open:opacity-100 hidden">
                            <div
                                class="block px-4 py-2 font-medium text-center text-default-700 rounded-t-lg bg-default-50">
                                Notifications
                            </div>

                            <div class="divide-y divide-default-100">
                                <a href="#" class="flex px-4 py-3 hover:bg-default-100">
                                    <div class="flex-shrink-0">
                                        <img class="rounded-full w-11 h-11" src="/assets/images/users/avatar-6.jpg"
                                            alt="Alex image">
                                        <div
                                            class="absolute flex items-center justify-center w-5 h-5 ms-6 -mt-5 bg-green-500 border border-white rounded-full">
                                            <i class="i-tabler-alert-circle text-white w-4 h-4"></i>
                                        </div>
                                    </div>
                                    <div class="w-full ps-3">
                                        <div class="text-default-500 text-sm mb-1.5">
                                            New alert from <span class="font-semibold text-default-900">Alex
                                                Johnson</span>:
                                            "System needs attention, check logs."
                                        </div>
                                        <div class="text-xs text-primary">2 minutes ago</div>
                                    </div>
                                </a>

                                <a href="#" class="flex px-4 py-3 hover:bg-default-100">
                                    <div class="flex-shrink-0">
                                        <img class="rounded-full w-11 h-11" src="/assets/images/users/avatar-7.jpg"
                                            alt="Sarah image">
                                        <div
                                            class="absolute flex items-center justify-center w-5 h-5 ms-6 -mt-5 bg-primary-600 border border-white rounded-full">
                                            <i class="i-tabler-file-text text-white w-4 h-4"></i>
                                        </div>
                                    </div>
                                    <div class="w-full ps-3">
                                        <div class="text-default-500 text-sm mb-1.5">
                                            <span class="font-semibold text-default-900">Sarah Lee</span> shared a
                                            document with you.
                                        </div>
                                        <div class="text-xs text-primary">5 minutes ago</div>
                                    </div>
                                </a>

                                <a href="#" class="flex px-4 py-3 hover:bg-default-100">
                                    <div class="flex-shrink-0">
                                        <img class="rounded-full w-11 h-11" src="/assets/images/users/avatar-8.jpg"
                                            alt="Michael image">
                                        <div
                                            class="absolute flex items-center justify-center w-5 h-5 ms-6 -mt-5 bg-purple-500 border border-white rounded-full">
                                            <i class="i-tabler-message text-white w-4 h-4"></i>
                                        </div>
                                    </div>
                                    <div class="w-full ps-3">
                                        <div class="text-default-500 text-sm mb-1.5">
                                            <span class="font-semibold text-default-900">Michael Clark</span> replied
                                            to your comment.
                                        </div>
                                        <div class="text-xs text-primary">15 minutes ago</div>
                                    </div>
                                </a>

                                <a href="#" class="flex px-4 py-3 hover:bg-default-100">
                                    <div class="flex-shrink-0">
                                        <img class="rounded-full w-11 h-11" src="/assets/images/users/avatar-9.jpg"
                                            alt="Emma image">
                                        <div
                                            class="absolute flex items-center justify-center w-5 h-5 ms-6 -mt-5 bg-pink-500 border border-white rounded-full">
                                            <i class="i-tabler-heart text-white w-4 h-4"></i>
                                        </div>
                                    </div>
                                    <div class="w-full ps-3">
                                        <div class="text-default-500 text-sm mb-1.5">
                                            <span class="font-semibold text-default-900">Emma Stone</span> reacted to
                                            your post.
                                        </div>
                                        <div class="text-xs text-primary">30 minutes ago</div>
                                    </div>
                                </a>
                            </div>


                            <a href="#"
                                class="block py-2 text-sm font-medium text-center text-default-900 rounded-b-lg bg-default-50 hover:bg-default-100">
                                <div class="inline-flex items-center ">
                                    <i class="i-tabler-eye size-4 text-default-500"></i>
                                    View all
                                </div>
                            </a>
                        </div>
                    </div> -->

                    <!-- Fullscreen Toggle Button -->
                    <!-- <div class="md:flex hidden">
                        <button data-toggle="fullscreen" type="button"
                            class="p-2 rounded-full bg-white border border-default-200 hover:bg-primary/15 hover:text-primary transition-all">
                            <span class="sr-only">Fullscreen Mode</span>
                            <span class="flex items-center justify-center size-6">
                                <i class="i-lucide-maximize text-2xl flex group-[-fullscreen]:hidden"></i>
                                <i class="i-lucide-minimize text-2xl hidden group-[-fullscreen]:flex"></i>
                            </span>
                        </button>
                    </div> -->

                    <!-- Profile Dropdown Button -->
                    <div class="relative">
                        <div class="relative inline-flex">
                            <button type="button" @click="toggleDropdown" class="focus:outline-none focus:ring-2 focus:ring-primary/20" style="display: flex; align-items: center; justify-content: center; gap: 10px;">
                                <img src="/assets/images/users/profil.jpg" alt="user-image" class="rounded-full h-10">
                                <span style="font-weight: 700;">{{ currentUser.name }}</span>
                            </button>
                            <div v-show="isDropdownOpen"
                                class="absolute right-0 mt-12 min-w-48 rounded-lg border border-default-200 bg-white p-2 shadow-md z-50"
                                :class="{ 'opacity-100': isDropdownOpen, 'opacity-0': !isDropdownOpen }">
                                <RouterLink class="flex items-center py-2 px-3 rounded-md text-sm text-default-800 hover:bg-default-100"
                                    to="/profile">
                                    Profile
                                </RouterLink>
                                <!-- <a class="flex items-center py-2 px-3 rounded-md text-sm text-default-800 hover:bg-default-100"
                                    href="#">
                                    Settings
                                </a>
                                <a class="flex items-center py-2 px-3 rounded-md text-sm text-default-800 hover:bg-default-100"
                                    href="#">
                                    Support
                                </a> -->

                                <hr class="my-2 -mx-2">

                                <a @click="LogoutFunction" class="flex items-center py-2 px-3 rounded-md text-sm text-default-800 hover:bg-default-100"
                                    style="cursor: pointer;">
                                    Log Out
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <!-- Topbar End -->
</template>
<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { isAuthenticated } from '../router'
import { postData } from '../plugins/api'
import { useRouter } from 'vue-router'

const isDropdownOpen = ref(false)
const isSidebarOpen = ref(false)

const currentUser = ref([])
const router = useRouter()

const GetCurrentUser = async ()=>{
    currentUser.value = await isAuthenticated()
}

const toggleDropdown = () => {
    isDropdownOpen.value = !isDropdownOpen.value
}

const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value
    const sidebar = document.getElementById('app-menu')
    if (sidebar) {
        if (isSidebarOpen.value) {
            sidebar.classList.remove('hidden', '-translate-x-full')
            sidebar.classList.add('translate-x-0')
            // Add backdrop
            const backdrop = document.createElement('div')
            backdrop.id = 'sidebar-backdrop'
            backdrop.className = 'fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden'
            backdrop.addEventListener('click', closeSidebar)
            document.body.appendChild(backdrop)
        } else {
            closeSidebar()
        }
    }
}

const closeSidebar = () => {
    isSidebarOpen.value = false
    const sidebar = document.getElementById('app-menu')
    if (sidebar) {
        sidebar.classList.add('hidden', '-translate-x-full')
        sidebar.classList.remove('translate-x-0')
    }
    // Remove backdrop
    const backdrop = document.getElementById('sidebar-backdrop')
    if (backdrop) {
        backdrop.remove()
    }
}

const closeDropdown = (event) => {
    // Close dropdown if clicking outside
    if (!event.target.closest('.relative')) {
        isDropdownOpen.value = false
    }
}

const LogoutFunction = async ()=>{
    await postData('/logout',null).then(res=>{
        if (res.status === 200) {
            localStorage.removeItem('token')
            currentUser.value = []
            router.push('/login')
        }
    })
}

onMounted(() => {
    document.addEventListener('click', closeDropdown)
    GetCurrentUser()
})

onUnmounted(() => {
    document.removeEventListener('click', closeDropdown)
})
</script>
<style scoped></style>