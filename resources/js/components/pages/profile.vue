<template>
    <main>
        
        <!-- Page Title Start -->
        <div class="flex items-center md:justify-between flex-wrap gap-2 mb-5">
            <h4 class="text-default-900 text-lg font-semibold">Profile</h4>

            <div class="md:flex hidden items-center gap-3 text-sm font-semibold">
                <RouterLink to="/" class="text-sm font-medium text-default-700">Home</RouterLink>

                <i class="i-tabler-chevron-right text-lg flex-shrink-0 text-default-500 rtl:rotate-180"></i>

                <RouterLink to="/customer" class="text-sm font-medium text-default-700" aria-current="page">Profile</RouterLink>
            </div>
        </div>
        <!-- Page Title End -->

        <div class="grid grid-cols-12 gap-6 mb-5">
            <div class="col-span-12">
                <div class="card p-5">
                    <h5 class="text-default-900 text-lg font-semibold mb-5">Your personal information</h5>
                    
                    <form @submit.prevent="UpdateProfile">
                        <div class="grid grid-cols-6 gap-4">
                            <div class="col-span-6 sm:col-span-3">
                                <label for="">Full Name :</label>
                                <input type="text" v-model="currentUser.name" class="form-input mt-1" placeholder="Enter your full name">
                            </div>
                            <div class="col-span-6 sm:col-span-3">
                                <label for="">Email :</label>
                                <input type="email" v-model="currentUser.email" class="form-input mt-1" placeholder="Enter your email">
                            </div>
                            <div class="col-span-6 sm:col-span-3">
                                <label for="">Role :</label>
                                <input type="text" v-model="currentUser.role" class="form-input mt-1" placeholder="Enter your role" disabled>
                            </div>
                            <div class="col-span-6 sm:col-span-3">
                                <label for="">Status :</label>
                                <input type="text" v-model="currentUser.status" class="form-input mt-1" placeholder="Enter your status" disabled>
                            </div>
                        </div>
                        <div class="mt-5 w-full text-end">
                            <button type="submit" class="btn bg-primary text-white">Update Profile</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-6">
            <div class="col-span-12">
                <div class="card p-5">
                    <h5 class="text-default-900 text-lg font-semibold mb-5">Change Password</h5>

                    <form @submit.prevent="UpdatePassword">
                        <div class="grid grid-cols-6 gap-4">
                            <div class="col-span-6 sm:col-span-3">
                                <label for="">Current Password :</label>
                                <input type="password" :class="{'border border-red-500':isEmpty.current_password}" v-model="data.current_password" class="form-input mt-1" placeholder="Enter your current password">
                                <span v-if="isEmpty.current_password" class="text-danger ">{{ msgInput.current_password }}</span>
                            </div>
                            <div class="col-span-6 sm:col-span-3">
                                <label for="">New Password :</label>
                                <input type="password" :class="{'border border-red-500':isEmpty.new_password}" v-model="data.new_password" class="form-input mt-1" placeholder="Enter your new password">
                                <span v-if="isEmpty.new_password" class="text-danger ">{{ msgInput.new_password }}</span>
                            </div>
                            <div class="col-span-6 ">
                                <label for="">Confirm New Password :</label>
                                <input type="password" :class="{'border border-red-500':isEmpty.new_password_confirmation}" v-model="data.new_password_confirmation" class="form-input mt-1" placeholder="Confirm your new password">
                                <span v-if="isEmpty.new_password_confirmation" class="text-danger ">{{ msgInput.new_password_confirmation }}</span>
                            </div>
                        </div>
                        <div class="mt-5 w-full text-end">
                            <button disabled v-if="isLoader" class="w-full mt-2 btn bg-primary text-white font-semibold py-2 rounded-lg hover:bg-blue-700 transition">
                                <div class="spinner-border text-light" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </button>
                            <button v-else type="submit" class="btn bg-primary text-white">Update Password</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

    </main>
</template>

<script setup>
    import { onMounted, ref } from 'vue';
    import { isAuthenticated } from '../router';
    import { putData } from '../plugins/api';
    import Swal from 'sweetalert2';

    const currentUser = ref({})

    const data = ref({
        current_password:"",
        new_password:"",
        new_password_confirmation:""
    })
    const isLoader = ref(false)
    const isEmpty = ref({})
    const msgInput = ref({})


    const GetCurrentUser = async ()=>{
        currentUser.value = await isAuthenticated()
    }

    const UpdateProfile = async ()=>{
       await putData('/updateusers/'+currentUser.value.id, currentUser.value).then(res=>{
            if(res.status == 200){
                Swal.fire({
                    toast: true,
                    position: "top-end",
                    icon: "success",
                    text:"Modification made",
                    timerProgressBar: true,
                    showConfirmButton: false,
                    timer: 3000
                })
            }
       }) 
    }

    const inputEmty = ()=>{
        if (data.value.current_password.trim() === '') {
            isEmpty.value.current_password = true
            msgInput.value.current_password = 'Ce champs est vide'
        }else{
            isEmpty.value.current_password = false
            msgInput.value.current_password = ''
        }

        if (data.value.new_password.trim() === '') {
            isEmpty.value.new_password = true
            msgInput.value.new_password = 'Ce champs est vide'
        }else{
            isEmpty.value.new_password = false
            msgInput.value.new_password = ''
        }

        if (data.value.new_password_confirmation.trim() === '') {
            isEmpty.value.new_password_confirmation = true
            msgInput.value.new_password_confirmation = 'Ce champs est vide'
        }else{
            isEmpty.value.new_password_confirmation = false
            msgInput.value.new_password_confirmation = ''
        }
    }

    const UpdatePassword = async ()=>{

        inputEmty()
        const allEmpty = Object.values(isEmpty.value).every(value => value === false)
        if (allEmpty){

            try{

                isLoader.value = true
                const res = await putData('/updatepwd/'+currentUser.value.id,data.value,{
                    headers:{
                        Authorization: `Bearer ${localStorage.getItem("token")}`
                    }
                })

                if(res.status === 200){
                    isLoader.value = false
                    Swal.fire({
                        toast: true,
                        position: "top-end",
                        icon: "success",
                        text:"Modification performed",
                        timerProgressBar: true,
                        showConfirmButton: false,
                        timer: 3000
                    })
                    data.value = {
                        current_password:"",
                        new_password:"",
                        new_password_confirmation:""
                    }
                }

            }catch(error){
                console.log(error)
                if(error.response.status === 403){
                    isLoader.value = false
                    isEmpty.value.current_password = true
                    msgInput.value.current_password = error.response.data.error
                }
            }

        }

    }

    onMounted(()=>{
        GetCurrentUser()
    })

</script>

<style scoped>
    
</style>