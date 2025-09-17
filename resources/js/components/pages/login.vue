<template>
    <div class="contain">
        <div class="card">
            <div class="card-body">
                <div style="display: flex; flex-direction: column; align-items: center; gap: 15px;">
                    <RouterLink to="/" class="mb-4" style="width: 100%; height: 40px; display: flex; justify-content: center; align-items: center;">
                        <img src="/assets/images/logo-dark.png" alt="" style="max-width: 100%; max-height: 100%; width: 100%; height: 100%; object-fit: contain;">
                    </RouterLink>
                    <h4 class="mb-12">Sign In to your Account</h4>
                    <p class="mb-32 text-secondary-light text-lg">Welcome back! please enter your detail</p>
                    <form style="width: 100%;" @submit.prevent="LoginForm">
                        <div class="mb-3">
                            <label for="exampleInputEmail1" class="text-default-800 text-sm font-medium inline-block mb-2">Email address</label>
                            <input type="email" :class="isEmpty.email ? 'is-invalid border border-danger' : ''" v-model="dataLogin.email" class="form-input" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Enter email">
                            <div v-if="isEmpty.email" class="invalid-feedback" style="color: red; font-size: 12px; margin-top: 5px;">{{ msgInput.email }}</div>
                        </div>
                        <div class="mb-4">
                            <label for="exampleInputPassword1" class="text-default-800 text-sm font-medium inline-block mb-2">Password</label>
                            <input type="password" :class="isEmpty.password ? 'is-invalid border border-danger' : ''" v-model="dataLogin.password" class="form-input" id="exampleInputPassword1" placeholder="Password">
                            <div v-if="isEmpty.password" class="invalid-feedback" style="color: red; font-size: 12px; margin-top: 5px;">{{ msgInput.password }}</div>
                        </div>
                        <div class="">
                            <div class="d-flex justify-content-between gap-2" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                                <div class="form-check style-check d-flex align-items-center">
                                    <!-- <input class="form-check-input border border-neutral-300" type="checkbox" value="" id="remeber">
                                    <label class="form-check-label" for="remeber">Remember me </label> -->
                                </div>
                                <router-link to="/admins/forgotPassword" class="text-primary-600 fw-medium">Forgot Password?</router-link>
                            </div>
                        </div>
                        
                        <button disabled v-if="isLoader" class="btn btn-primary text-sm btn-sm px-12 py-16 w-100 radius-12 mt-32">
                            <div class="spinner-border text-light" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </button>
                        <button v-else type="submit" class="btn bg-primary text-white">Submit</button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</template>
<script setup>

    import {ref} from 'vue';
    import { useRouter } from 'vue-router';
    import {postData} from '../plugins/api';

    const dataLogin = ref({
        email:"",
        password: ""
    })
    const isEmpty = ref({})
    const msgInput = ref({})
    const isLoader = ref(false)
    const router = useRouter()

    const inputIsEmpty = ()=>{
        if (dataLogin.value.email.trim() === "") {
            isEmpty.value.email = true
            msgInput.value.email = "Veuillez saisie l'adresse email"
        }else{
            isEmpty.value.email = false
            msgInput.value.email = ""
        }
        if (dataLogin.value.password.trim() === "") {
            isEmpty.value.password = true
            msgInput.value.password = "Veuillez saisie votre mot de passe"
        }else{
            isEmpty.value.password = false
            msgInput.value.password = ""
        }
    }

    const LoginForm = async ()=>{
        inputIsEmpty()
        const allEmpty = Object.values(isEmpty.value).every(value => value === false)
        if (allEmpty) {
            try {

                isLoader.value = true
                await postData('/login',dataLogin.value)
                    .then(res => {
                        if (res.status === 200) {
                            localStorage.setItem('token', res.data.token)
                            isLoader.value = false
                            // Rediriger vers la route sauvegardée ou par défaut
                            const redirectUrl = localStorage.getItem('redirectAfterLogin');
                            if (redirectUrl) {
                                // Forcer une redirection complète du navigateur
                                window.location.href = redirectUrl;
                                localStorage.removeItem('redirectAfterLogin');
                            } else {
                                //router.push('/');
                                window.location.href = "/"
                            }
                        }
                    })

            } catch (error) {
                if (error.response) {
                    if (error.response.status === 401) {
                        isLoader.value = false
                        isEmpty.value.email = true
                        isEmpty.value.password = true
                        msgInput.value.email = error.response.data.message
                        msgInput.value.password = error.response.data.message
                    } else {
                        console.error("Erreur du serveur :", error.response.data.message || "Veuillez réessayer plus tard.");
                    }
                }
            }
        }
    }

</script>
<style scoped>
    .contain{
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }
</style>