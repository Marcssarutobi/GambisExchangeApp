<template>
    <main>
        <!-- Page Title Start -->
        <div class="flex items-center md:justify-between flex-wrap gap-2 mb-5">
            <h4 class="text-default-900 text-lg font-semibold">
                Comptes de {{ client?.nom }} {{ client?.prenom }}
            </h4>

            <div class="md:flex hidden items-center gap-3 text-sm font-semibold">
                <RouterLink to="/" class="text-sm font-medium text-default-700">Home</RouterLink>
                <i class="material-symbols-rounded text-lg flex-shrink-0 text-default-500 rtl:rotate-180">chevron_right</i>
                <RouterLink to="/customer" class="text-sm font-medium text-default-700">Customer list</RouterLink>
                <i class="material-symbols-rounded text-lg flex-shrink-0 text-default-500 rtl:rotate-180">chevron_right</i>
                <span class="text-sm font-medium text-default-700" aria-current="page">Comptes</span>
            </div>
        </div>
        <!-- Page Title End -->

        <div class="mb-4">
            <RouterLink to="/customer" class="inline-flex items-center gap-2 text-sm font-medium text-primary hover:underline">
                <i class="material-symbols-rounded">arrow_back</i> Retour à la liste des clients
            </RouterLink>
        </div>

        <div v-if="loading" class="text-center text-default-500 py-10">
            Chargement des comptes...
        </div>

        <div v-else-if="accounts.length === 0" class="rounded-xl border border-dashed border-gray-300 bg-white p-8 text-center text-default-500">
            <i class="material-symbols-rounded text-3xl mb-2 block">account_balance_wallet</i>
            Ce client n'a pas encore de compte.
        </div>

        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div v-for="account in accounts" :key="account.id"
                class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm hover:shadow-md transition-all">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-default-500">Compte</p>
                        <p class="font-semibold text-default-900">{{ account.code }}</p>
                    </div>
                    <span class="text-xs font-semibold px-2 py-1 rounded-full bg-primary/10 text-primary">
                        {{ account.currency?.code }}
                    </span>
                </div>

                <p class="text-2xl font-bold text-default-900 mb-4">
                    {{ Number(account.balance).toLocaleString('fr-FR') }}
                    <span class="text-sm font-medium text-default-500">{{ account.currency?.code }}</span>
                </p>

                <div class="grid grid-cols-2 gap-2">
                    <button @click="goToOperation(account, 'deposit')"
                        class="flex items-center justify-center gap-2 rounded-md bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium py-2 transition-all">
                        <i class="material-symbols-rounded">add_circle</i> Créditer
                    </button>
                    <button @click="goToOperation(account, 'withdraw')"
                        class="flex items-center justify-center gap-2 rounded-md bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium py-2 transition-all">
                        <i class="material-symbols-rounded">remove_circle</i> Débiter
                    </button>
                </div>
            </div>
        </div>
    </main>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { getData } from '../plugins/api';

const route = useRoute();
const router = useRouter();

const client = ref(null);
const accounts = ref([]);
const loading = ref(true);

async function loadAccounts() {
    loading.value = true;
    try {
        const res = await getData(`/clients/${route.params.id}/accounts`);
        client.value = res.data.data.client;
        accounts.value = res.data.data.accounts;
    } catch (error) {
        console.error('Error fetching client accounts:', error);
    } finally {
        loading.value = false;
    }
}

// Point 2 : un clic sur Créditer/Débiter ouvre directement le formulaire d'opération,
// avec le compte et le type déjà pré-remplis.
function goToOperation(account, type) {
    router.push({ path: '/exchange', query: { account_id: account.id, type } });
}

onMounted(() => {
    loadAccounts();
});
</script>

<style scoped></style>
