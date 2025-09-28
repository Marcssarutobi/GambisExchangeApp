<template>
	<div class="min-h-screen flex items-center justify-center bg-gray-100">
		<div class="bg-white shadow-lg rounded-lg p-8 w-full max-w-md text-center">
			<svg class="mx-auto mb-4 w-16 h-16 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12A9 9 0 1 1 3 12a9 9 0 0 1 18 0z" />
			</svg>
			<h2 class="text-2xl font-bold mb-2 text-gray-800">Unauthorized access</h2>
			<p class="mb-6 text-gray-600">You do not have permission to access this page.</p>
			<RouterLink to="/" class="inline-block bg-blue-600 text-white font-semibold py-2 px-6 rounded-lg hover:bg-blue-700 transition">Return to Home</RouterLink>
		</div>
	</div>
</template>

<script setup>
// Ajoute ici la logique de redirection si besoin
</script>

<style scoped>
/* Ajoute ici des styles personnalisés si besoin */
</style>
