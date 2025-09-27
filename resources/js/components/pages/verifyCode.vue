<template>
	<div class="min-h-screen flex items-center justify-center bg-gray-100">
		<div class="bg-white shadow-lg rounded-lg p-8 w-full max-w-md">
			<h2 class="text-2xl font-bold mb-6 text-gray-800 text-center">Code verification</h2>
			<form @submit.prevent="VerifyCode">
				<label class="block mb-2 text-sm font-medium text-gray-700" for="resetCode">
					Reset Code
				</label>
				<input 
                    id="resetCode"
					type="text"
                    v-model="data.reset_code"
                    :class="{'border border-red-500':isEmpty.reset_code}"
					class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 mb-2 tracking-widest text-center"
					placeholder="Enter the received code"
				/>
                <span v-if="isEmpty.reset_code" class="text-danger ">{{ msgInput.reset_code }}</span>

                <button disabled v-if="isLoader" class="w-full mt-2 btn bg-primary text-white font-semibold py-2 rounded-lg hover:bg-blue-700 transition">
                    <div class="spinner-border text-light" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </button>
				<button v-else
					type="submit"
					class="w-full mt-2 bg-primary-600 text-white font-semibold py-2 rounded-lg hover:bg-blue-700 transition"
				>
					Check the code
				</button>
			</form>
		</div>
	</div>
</template>

<script setup>

    import { onMounted, ref, reactive } from 'vue';
    import { RouterLink, useRouter } from 'vue-router';
    import Swal from 'sweetalert2';
    import { postData } from '../plugins/api';
    import { useUserStore } from '../store/user';

    const data = ref({
        reset_code: "",
        email:''
    })

    const isEmpty = ref({})
    const msgInput = ref({})
    const isLoader = ref(false)

    const userStore = useUserStore()

    const inputIsEmpty = () => {
        if (data.value.reset_code.trim() === "") {
            isEmpty.value.reset_code = true
            msgInput.value.reset_code = "Please enter the received code"
        } else {
            isEmpty.value.reset_code = false
            msgInput.value.reset_code = ""
        }

        if (data.value.email.trim() === "") {
            isEmpty.value.email = true
            msgInput.value.email = "Please enter your email address"
        } else {
            isEmpty.value.email = false
            msgInput.value.email = ""
        }
    }

    const VerifyCode = async () => {
        data.value.email = userStore.user?.email
        inputIsEmpty()
        const allEmpty = Object.values(isEmpty.value).every(value => value === false)
        if (allEmpty) {
            try {
                isLoader.value = true
                

                const res = await postData('/verifycode', data.value)
                if (res.status === 200) {
                    isLoader.value = false
                    localStorage.setItem('token', res.data.token)
                    data.value.email = ""
                    data.value.reset_code = ""
                    Swal.fire({
                        toast: true,
                        position: "top-end",
                        icon: "success",
                        text: "Code successfully verified and login completed",
                        timerProgressBar: true,
                        showConfirmButton: false,
                        timer: 3000
                    })
                    addmodal.hide()
                    window.location.href = "/changePassword"
                }
            } catch (error) {
                console.log(error)
            }
        }else{
            console.log(data.value)
        }
    }

</script>

<style scoped>
/* Ajoute ici des styles personnalisés si besoin */
</style>
