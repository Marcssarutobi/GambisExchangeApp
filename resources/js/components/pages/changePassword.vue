<template>
	<div class="min-h-screen flex items-center justify-center bg-gray-100">
		<div class="bg-white shadow-lg rounded-lg p-8 w-full max-w-md">
			<h2 class="text-2xl font-bold mb-6 text-gray-800 text-center">Change password</h2>
			<form>
				<label class="block mb-2 text-sm font-medium text-gray-700" for="newPassword">
					New password
				</label>
				<input
					id="newPassword"
					type="password"
					class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 mb-4"
					placeholder="Nouveau mot de passe"
					required
				/>
				<label class="block mb-2 text-sm font-medium text-gray-700" for="confirmPassword">
					Confirm password
				</label>
				<input
					id="confirmPassword"
					type="password"
					class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 mb-4"
					placeholder="Confirmer le mot de passe"
					required
				/>
				<button
					type="submit"
					class="w-full bg-primary-600 text-white font-semibold py-2 rounded-lg hover:bg-blue-700 transition"
				>
					Change password
				</button>
			</form>
		</div>
	</div>
</template>

<script setup>
// Ajoute ici la logique de modification du mot de passe si besoin
</script>

<style scoped>
/* Ajoute ici des styles personnalisés si besoin */
</style>
