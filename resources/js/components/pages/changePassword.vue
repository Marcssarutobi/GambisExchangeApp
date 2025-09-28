<template>
	<div class="min-h-screen flex items-center justify-center bg-gray-100">
		<div class="bg-white shadow-lg rounded-lg p-8 w-full max-w-md">
			<h2 class="text-2xl font-bold mb-6 text-gray-800 text-center">Change password</h2>
			<form @submit.prevent="resetPassword">
				<label class="block mb-2 text-sm font-medium text-gray-700" for="newPassword">
					New password
				</label>
				<input
					id="newPassword"
					type="password"
					v-model="data.password"
					:class="{'border border-red-500':isEmpty.password}"
					class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 mb-2"
					placeholder="Nouveau mot de passe"
					
				/>
				<span v-if="isEmpty.password" class="text-danger ">{{ msgInput.password }}</span>
				<label class="block mb-2 text-sm font-medium text-gray-700 mt-2" for="confirmPassword">
					Confirm password
				</label>
				<input
					id="confirmPassword"
					type="password"
					v-model="data.password_confirmation"
					:class="{'border border-red-500':isEmpty.password_confirmation}"
					class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 mb-2"
					placeholder="Confirmer le mot de passe"
					
				/>
				<span v-if="isEmpty.password_confirmation" class="text-danger ">{{ msgInput.password_confirmation }}</span>

				<button disabled v-if="isLoader" class="w-full mt-2 btn bg-primary text-white font-semibold py-2 rounded-lg hover:bg-blue-700 transition">
					<div class="spinner-border text-light" role="status">
						<span class="visually-hidden">Loading...</span>
					</div>
				</button>
				<button
					type="submit"
					v-else
					class="w-full bg-primary-600 text-white font-semibold py-2 rounded-lg hover:bg-blue-700 transition mt-2"
				>
					Change password
				</button>
			</form>
		</div>
	</div>
</template>

<script setup>

	import { onMounted, ref } from 'vue';
    import Swal from 'sweetalert2';
    import { useRouter } from 'vue-router';
	import { putData } from '../plugins/api';
	import { isAuthenticated } from '../router';

    const currentUser = ref({})
  
    const data = ref({
        password:"",
        password_confirmation:"",
    })
    const isEmpty = ref({})
    const msgInput = ref({})
    const isLoader = ref(false)
    const router = useRouter()

	const inputEmty = ()=>{
        if (data.value.password.trim() === '') {
            isEmpty.value.password = true
            msgInput.value.password = 'This field is empty.'
        }else{
            isEmpty.value.password = false
            msgInput.value.password = ''
        }
        if (data.value.password_confirmation.trim() === '') {
            isEmpty.value.password_confirmation = true
            msgInput.value.password_confirmation = 'This field is empty.'
        }else{
            isEmpty.value.password_confirmation = false
            msgInput.value.password_confirmation = ''
        }
    }

	const resetPassword = async ()=>{
        inputEmty()
        const allEmpty = Object.values(isEmpty.value).every(value => value === false)
        if (allEmpty) {
            try {
                isLoader.value = true
                const user = await isAuthenticated()
                const res = await putData('/resetpasswords/'+user.id,data.value,{
                    headers: {
                        Authorization: `Bearer ${localStorage.getItem("token")}`,
                    },
                })
                if (res.status === 200) {
                    isLoader.value = false
                    Swal.fire({
                        toast: true,
                        position: "top-end",
                        icon: "success",
                        text:"Modification made",
                        timerProgressBar: true,
                        showConfirmButton: false,
                        timer: 3000
                    })
                    data.value= {
                        password:"",
                        password_confirmation:"",
                    }
                    router.push('/')
                }
            } catch (error) {
                console.log(error)
                if (error.response.status === 422) {
                    isLoader.value = false
                    isEmpty.value.password_confirmation = true
                    msgInput.value.password_confirmation = "You did not enter the same password"
                }
            }
        }else{
			console.log(data.value)
		}
    }




</script>

<style scoped>
/* Ajoute ici des styles personnalisés si besoin */
</style>
