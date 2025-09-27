<template>
	<div class="min-h-screen flex items-center justify-center bg-gray-100">
		<div class="bg-white shadow-lg rounded-lg p-8 w-full max-w-md">
			<h2 class="text-2xl font-bold mb-6 text-gray-800 text-center">Forgot Password ?</h2>
			<form @submit.prevent="sendCodeMail">
				<label class="block mb-2 text-sm font-medium text-gray-700" for="email">Email address</label>
				<input id="email" :class="{'border border-red-500':isEmpty.email}" type="email" v-model="data.email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 mb-2" placeholder="Enter your email address"  />
                <span v-if="isEmpty.email" class="text-danger ">{{ msgInput.email }}</span>

                <button disabled v-if="isLoader" class="w-full mt-2 btn bg-primary text-white font-semibold py-2 rounded-lg hover:bg-blue-700 transition">
                    <div class="spinner-border text-light" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </button>
				<button v-else type="submit" class="w-full mt-2 btn bg-primary text-white font-semibold py-2 rounded-lg hover:bg-blue-700 transition">
					Send Reset Code
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

    const router = useRouter();
    const userStore = useUserStore()
    const data = ref({
        email: ""
    })

    const isEmpty = ref({})
    const msgInput = ref({})
    const isLoader = ref(false)
    const getuser = ref({})

    const inputIsEmpty = () => {
        if (data.value.email.trim() === "") {
            isEmpty.value.email = true
            msgInput.value.email = "Please enter your email address"
        } else {
            isEmpty.value.email = false
            msgInput.value.email = ""
        }
    }

    const sendCodeMail = async () => {
        inputIsEmpty()
        const allEmpty = Object.values(isEmpty.value).every(value => value === false)
        if (allEmpty) {
            try {
                isLoader.value = true
                const res = await postData('/sendcode', data.value)
                if (res.status === 200) {
                    isLoader.value = false
                    userStore.setUser(res.data.user)
                    router.push('/verify-code')
                }
            } catch (error) {
                console.log(error)
                if (error.response.status === 404) {
                    isLoader.value = false
                    isEmpty.value.email = true
                    msgInput.value.email = error.response.data.message
                }
            }
        }
    }

</script>
<style scoped>
/* Ajoute ici des styles personnalisés si besoin */
</style>