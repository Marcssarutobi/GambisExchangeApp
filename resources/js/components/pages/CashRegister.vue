<template>
    <main>
        <!-- Page Title Start -->
        <div class="flex items-center md:justify-between flex-wrap gap-2 mb-5">
            <h4 class="text-default-900 text-lg font-semibold">Caisse générale</h4>

            <div class="md:flex hidden items-center gap-3 text-sm font-semibold">
                <RouterLink to="/" class="text-sm font-medium text-default-700">Home</RouterLink>
                <i class="material-symbols-rounded text-lg flex-shrink-0 text-default-500 rtl:rotate-180">chevron_right</i>
                <span class="text-sm font-medium text-default-700" aria-current="page">Caisse générale</span>
            </div>
        </div>
        <!-- Page Title End -->

        <!-- Soldes par devise -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div v-for="register in balances" :key="register.id"
                class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs uppercase tracking-wide text-default-500">Caisse {{ register.currency?.code }}</p>
                    <i class="material-symbols-rounded text-primary">account_balance</i>
                </div>
                <p class="text-2xl font-bold" :class="register.balance < 0 ? 'text-rose-600' : 'text-default-900'">
                    {{ Number(register.balance).toLocaleString('fr-FR') }}
                    <span class="text-sm font-medium text-default-500">{{ register.currency?.code }}</span>
                </p>
            </div>
            <div v-if="balances.length === 0" class="col-span-full text-center text-default-500 py-6 rounded-xl border border-dashed border-gray-300 bg-white">
                Aucune caisse enregistrée pour le moment (elle se crée automatiquement au premier mouvement).
            </div>
        </div>

        <!-- Ajustement manuel -->
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm mb-6">
            <p class="font-semibold text-default-900 mb-3 flex items-center gap-2">
                <i class="material-symbols-rounded">tune</i> Ajustement manuel (écart de comptage)
            </p>
            <form @submit.prevent="submitAdjustment" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Devise</label>
                    <select v-model="adjustment.currency_id" class="w-full border border-gray-300 rounded-md p-2 text-sm">
                        <option value="">Sélectionner</option>
                        <option v-for="c in allCurrency" :key="c.id" :value="c.id">{{ c.code }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Sens</label>
                    <select v-model="adjustment.direction" class="w-full border border-gray-300 rounded-md p-2 text-sm">
                        <option value="in">Entrée (+)</option>
                        <option value="out">Sortie (-)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Montant</label>
                    <input type="number" min="0.01" step="0.01" v-model="adjustment.amount" class="w-full border border-gray-300 rounded-md p-2 text-sm">
                </div>
                <div class="lg:col-span-1">
                    <label class="block text-xs text-gray-500 mb-1">Note</label>
                    <input type="text" v-model="adjustment.note" placeholder="Ex: écart de comptage physique" class="w-full border border-gray-300 rounded-md p-2 text-sm">
                </div>
                <button type="submit" :disabled="adjusting"
                    class="bg-primary text-white rounded-md shadow-sm py-2 text-sm font-medium hover:opacity-90 transition-all">
                    <i class="material-symbols-rounded me-1">check_circle</i> {{ adjusting ? 'Enregistrement...' : 'Enregistrer' }}
                </button>
            </form>
        </div>

        <!-- Historique -->
        <div class="rounded-xl border border-gray-200 bg-white p-3 shadow-sm">
            <div class="flex items-center justify-between flex-wrap gap-3 mb-4 px-2 pt-2">
                <p class="font-semibold text-default-900 flex items-center gap-2">
                    <i class="material-symbols-rounded">history</i> Historique de caisse
                </p>
                <button @click="exportHistoryToExcel"
                    class="px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded-md shadow-sm">
                    <i class="material-symbols-rounded me-1">download</i> Exporter en Excel
                </button>
            </div>

            <!-- Point 6 : filtre de dates + devise. Barre compacte (largeurs fixes, pas étirées). -->
            <div class="flex flex-wrap items-end gap-3 mb-4 px-2">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Devise</label>
                    <select v-model="filters.currency_id" @change="loadHistory" class="w-40 border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
                        <option value="">Toutes les devises</option>
                        <option v-for="c in allCurrency" :key="c.id" :value="c.id">{{ c.code }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Du</label>
                    <input type="date" v-model="filters.from" @change="loadHistory" class="w-40 border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Au</label>
                    <input type="date" v-model="filters.to" @change="loadHistory" class="w-40 border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
                </div>
            </div>

            <!-- Même composant DataTable (Bootstrap 5) que le reste de l'application,
                 pour un rendu cohérent (au lieu d'un <table> brut non stylé). -->
            <DataTable :data="history" :columns="historyColumns" :DeleteAllFunction="() => {}" />
        </div>
    </main>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { getData, postData } from '../plugins/api';
import Swal from 'sweetalert2';
import * as XLSX from 'xlsx';
import DataTable from '../layout/Datatable.vue';

const balances = ref([]);
const history = ref([]);
const allCurrency = ref([]);
const adjusting = ref(false);

const filters = ref({ currency_id: '', from: '', to: '' });
const adjustment = ref({ currency_id: '', direction: 'in', amount: '', note: '' });

function typeLabel(type) {
    const labels = {
        client_deposit: 'Dépôt client',
        client_withdraw: 'Retrait client',
        purchase_in: 'Achat devise (entrée)',
        purchase_out: 'Achat devise (sortie)',
        sale_in: 'Vente devise (entrée)',
        sale_out: 'Vente devise (sortie)',
        adjustment: 'Ajustement manuel',
    };
    return labels[type] ?? type;
}

function formatDate(value) {
    return new Intl.DateTimeFormat('fr-FR', {
        year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit',
    }).format(new Date(value));
}

const historyColumns = [
    { title: 'Devise', data: null, render: (data, type, row) => row.cash_register?.currency?.code ?? '-' },
    { title: 'Type', data: null, render: (data, type, row) => typeLabel(row.type) },
    { title: 'Montant', data: null, render: (data, type, row) => Number(row.amount).toLocaleString('fr-FR') },
    { title: 'Solde avant', data: null, render: (data, type, row) => Number(row.balance_before).toLocaleString('fr-FR') },
    { title: 'Solde après', data: null, render: (data, type, row) => Number(row.balance_after).toLocaleString('fr-FR') },
    { title: 'Note', data: null, render: (data, type, row) => row.note ?? '-' },
    { title: 'Date', data: null, render: (data, type, row) => formatDate(row.created_at) },
];

async function loadBalances() {
    const res = await getData('/cash-registers');
    balances.value = res.data.data;
}

async function loadCurrencies() {
    const res = await getData('/currencies');
    allCurrency.value = res.data.data;
}

async function loadHistory() {
    const params = new URLSearchParams();
    if (filters.value.currency_id) params.append('currency_id', filters.value.currency_id);
    if (filters.value.from) params.append('from', filters.value.from);
    if (filters.value.to) params.append('to', filters.value.to);
    const res = await getData(`/cash-registers/history?${params.toString()}`);
    history.value = res.data.data;
}

async function submitAdjustment() {
    if (!adjustment.value.currency_id || !adjustment.value.amount) {
        Swal.fire({ icon: 'warning', text: 'Devise et montant sont requis.', timer: 2000, showConfirmButton: false });
        return;
    }
    adjusting.value = true;
    try {
        await postData('/cash-registers/adjust', adjustment.value);
        Swal.fire({ icon: 'success', text: 'Ajustement enregistré', showConfirmButton: false, timer: 1500 });
        adjustment.value = { currency_id: '', direction: 'in', amount: '', note: '' };
        await loadBalances();
        await loadHistory();
    } catch (error) {
        Swal.fire({ icon: 'error', text: error.response?.data?.message ?? 'Une erreur est survenue', showConfirmButton: false, timer: 2000 });
    } finally {
        adjusting.value = false;
    }
}

// Point 6 : export de l'historique de caisse filtré, généré côté client (bibliothèque xlsx déjà utilisée dans l'app).
function exportHistoryToExcel() {
    const rows = history.value.map(m => ({
        Devise: m.cash_register?.currency?.code,
        Type: typeLabel(m.type),
        Montant: m.amount,
        'Solde avant': m.balance_before,
        'Solde après': m.balance_after,
        Note: m.note ?? '-',
        Date: formatDate(m.created_at),
    }));
    const worksheet = XLSX.utils.json_to_sheet(rows);
    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, 'Historique caisse');
    XLSX.writeFile(workbook, `Historique_Caisse_${new Date().toISOString().slice(0, 10)}.xlsx`);
}

onMounted(async () => {
    await loadCurrencies();
    await loadBalances();
    await loadHistory();
});
</script>

<style scoped></style>
