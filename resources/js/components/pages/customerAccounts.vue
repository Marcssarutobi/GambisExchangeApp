<template>
    <main>

        <!-- Page Title Start -->
        <div class="flex items-center md:justify-between flex-wrap gap-2 mb-5">
            <h4 class="text-default-900 text-lg font-semibold">
                Comptes de {{ client.nom }} {{ client.prenom }}
            </h4>

            <div class="md:flex hidden items-center gap-3 text-sm font-semibold">
                <RouterLink to="/" class="text-sm font-medium text-default-700">Home</RouterLink>
                <i class="i-tabler-chevron-right text-lg flex-shrink-0 text-default-500 rtl:rotate-180"></i>
                <RouterLink to="/customer" class="text-sm font-medium text-default-700">Customer list</RouterLink>
                <i class="i-tabler-chevron-right text-lg flex-shrink-0 text-default-500 rtl:rotate-180"></i>
                <span class="text-sm font-medium text-default-700">Comptes</span>
            </div>
        </div>
        <!-- Page Title End -->

        <!-- Infos client -->
        <div class="card p-3 mb-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-sm">
                <div><span class="font-semibold">Nom :</span> {{ client.nom }} {{ client.prenom }}</div>
                <div><span class="font-semibold">Téléphone :</span> {{ client.phone ?? '-' }}</div>
                <div><span class="font-semibold">Email :</span> {{ client.email ?? '-' }}</div>
                <div><span class="font-semibold">Pièce :</span> {{ client.npiece ?? '-' }}</div>
            </div>
        </div>

        <div class="card overflow-hidden p-3">

            <!-- Sélecteur de compte + filtres -->
            <div class="flex flex-wrap items-end gap-3 mb-4">

                <div v-if="accounts.length > 1" class="min-w-[220px]">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Compte</label>
                    <select v-model="selectedAccountId" class="block w-full border border-gray-300 rounded-md p-2">
                        <option v-for="acc in accounts" :key="acc.id" :value="acc.id">
                            {{ acc.code }} — {{ acc.currency?.code }}
                        </option>
                    </select>
                </div>

                <div v-else-if="accounts.length === 1" class="text-sm">
                    <span class="font-semibold">Compte :</span>
                    {{ accounts[0].code }} — {{ accounts[0].currency?.code }}
                </div>

                <div class="min-w-[160px]">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Du</label>
                    <input type="date" v-model="filters.from" class="block w-full border border-gray-300 rounded-md p-2">
                </div>

                <div class="min-w-[160px]">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Au</label>
                    <input type="date" v-model="filters.to" class="block w-full border border-gray-300 rounded-md p-2">
                </div>

                <button type="button" @click="applyFilter" class="btn bg-primary text-white">
                    Filtrer
                </button>

                <button type="button" @click="resetFilter" class="btn bg-gray-200 text-dark">
                    Réinitialiser
                </button>

                <button type="button" @click="exportToPDF" :disabled="!movements.length"
                    class="btn bg-green-600 text-white ms-auto disabled:opacity-50">
                    📄 Exporter en PDF
                </button>
            </div>

            <div v-if="loadingAccounts" class="text-sm text-gray-500 py-6 text-center">
                Chargement des comptes...
            </div>

            <div v-else-if="!accounts.length" class="text-sm text-gray-500 py-6 text-center">
                Ce client n'a aucun compte.
            </div>

            <div v-else class="overflow-x-auto">
                <div v-if="loadingHistory" class="text-sm text-gray-500 py-6 text-center">
                    Chargement de l'historique...
                </div>

                <table v-else class="min-w-full border border-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2 text-left text-gray-700">Date</th>
                            <th class="px-4 py-2 text-left text-gray-700">Réf.</th>
                            <th class="px-4 py-2 text-left text-gray-700">Description</th>
                            <th class="px-4 py-2 text-right text-gray-700">Débit</th>
                            <th class="px-4 py-2 text-right text-gray-700">Crédit</th>
                            <th class="px-4 py-2 text-right text-gray-700">Solde</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!movements.length">
                            <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                                Aucune transaction sur cette période.
                            </td>
                        </tr>

                        <tr v-for="(mvt, index) in movements" :key="mvt.id"
                            :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50'">
                            <td class="px-4 py-2">{{ formatDateTime(mvt.created_at) }}</td>
                            <td class="px-4 py-2">MVT-{{ mvt.id }}</td>
                            <td class="px-4 py-2">
                                {{ mvt.performed_by ?? (mvt.type === 'deposit' ? 'Dépôt' : 'Retrait') }}
                            </td>
                            <td class="px-4 py-2 text-right">
                                <span v-if="mvt.type === 'withdraw'" class="text-red-600 font-semibold">
                                    {{ formatAmount(mvt.final_amount) }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-right">
                                <span v-if="mvt.type === 'deposit'" class="text-green-600 font-semibold">
                                    {{ formatAmount(mvt.final_amount) }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-right font-semibold"
                                :class="mvt.balance_after >= 0 ? 'text-green-600' : 'text-red-600'">
                                {{ formatAmount(Math.abs(mvt.balance_after)) }} {{ mvt.balance_after >= 0 ? 'CR' : 'DR' }}
                            </td>
                        </tr>

                        <tr v-if="movements.length" class="bg-gray-100 font-semibold">
                            <td class="px-4 py-2" colspan="5">Solde d'ouverture de la période</td>
                            <td class="px-4 py-2 text-right"
                                :class="openingBalance >= 0 ? 'text-green-600' : 'text-red-600'">
                                {{ formatAmount(Math.abs(openingBalance)) }} {{ openingBalance >= 0 ? 'CR' : 'DR' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div> <!-- end card -->

    </main>
</template>

<script setup>

import { onMounted, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import { getData, getSingleData } from '../plugins/api';
import pdfMake from 'pdfmake/build/pdfmake';
import pdfFonts from 'pdfmake/build/vfs_fonts';

// Compatibilité entre versions de pdfmake pour l'enregistrement des polices
pdfMake.vfs = (pdfFonts && pdfFonts.pdfMake && pdfFonts.pdfMake.vfs) ? pdfFonts.pdfMake.vfs : pdfFonts.vfs;

const route = useRoute();
const clientId = route.params.id;

const client = ref({});
const accounts = ref([]);
const selectedAccountId = ref(null);
const movements = ref([]);
const openingBalance = ref(0);

const loadingAccounts = ref(false);
const loadingHistory = ref(false);

const filters = ref({
    from: '',
    to: '',
});

function formatAmount(value) {
    return Number(value ?? 0).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatDateTime(dateString) {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(date);
}

async function fetchClient() {
    loadingAccounts.value = true;
    try {
        const res = await getSingleData(`/clients/${clientId}`);
        client.value = res.data.data;
        accounts.value = client.value.accounts ?? [];

        if (accounts.value.length) {
            selectedAccountId.value = accounts.value[0].id;
        }
    } catch (error) {
        console.error('Erreur lors du chargement du client :', error);
    } finally {
        loadingAccounts.value = false;
    }
}

async function fetchHistory() {
    if (!selectedAccountId.value) return;

    loadingHistory.value = true;
    try {
        const params = new URLSearchParams();
        if (filters.value.from) params.append('from', filters.value.from);
        if (filters.value.to) params.append('to', filters.value.to);

        const query = params.toString() ? `?${params.toString()}` : '';
        const res = await getData(`/movements/account/${selectedAccountId.value}${query}`);

        movements.value = res.data.data.movements;
        openingBalance.value = res.data.data.opening_balance;
    } catch (error) {
        console.error("Erreur lors du chargement de l'historique :", error);
    } finally {
        loadingHistory.value = false;
    }
}

function applyFilter() {
    fetchHistory();
}

function resetFilter() {
    filters.value.from = '';
    filters.value.to = '';
    fetchHistory();
}

function getSelectedAccount() {
    return accounts.value.find(acc => acc.id === selectedAccountId.value) || {};
}

function exportToPDF() {
    const account = getSelectedAccount();
    const periode = (filters.value.from || filters.value.to)
        ? `Du ${filters.value.from || '...'} au ${filters.value.to || "aujourd'hui"}`
        : 'Historique complet';

    const body = [
        [
            { text: 'Date', style: 'tableHeader' },
            { text: 'Réf.', style: 'tableHeader' },
            { text: 'Description', style: 'tableHeader' },
            { text: 'Débit', style: 'tableHeader', alignment: 'right' },
            { text: 'Crédit', style: 'tableHeader', alignment: 'right' },
            { text: 'Solde', style: 'tableHeader', alignment: 'right' },
        ],
    ];

    movements.value.forEach((mvt) => {
        body.push([
            formatDateTime(mvt.created_at),
            `MVT-${mvt.id}`,
            mvt.performed_by ?? (mvt.type === 'deposit' ? 'Dépôt' : 'Retrait'),
            {
                text: mvt.type === 'withdraw' ? formatAmount(mvt.final_amount) : '',
                color: 'red',
                bold: true,
                alignment: 'right',
            },
            {
                text: mvt.type === 'deposit' ? formatAmount(mvt.final_amount) : '',
                color: 'green',
                bold: true,
                alignment: 'right',
            },
            {
                text: `${formatAmount(Math.abs(mvt.balance_after))} ${mvt.balance_after >= 0 ? 'CR' : 'DR'}`,
                color: mvt.balance_after >= 0 ? 'green' : 'red',
                bold: true,
                alignment: 'right',
            },
        ]);
    });

    body.push([
        { text: "Solde d'ouverture de la période", colSpan: 5, bold: true },
        {}, {}, {}, {},
        {
            text: `${formatAmount(Math.abs(openingBalance.value))} ${openingBalance.value >= 0 ? 'CR' : 'DR'}`,
            bold: true,
            alignment: 'right',
            color: openingBalance.value >= 0 ? 'green' : 'red',
        },
    ]);

    const docDefinition = {
        pageOrientation: 'landscape',
        content: [
            { text: 'GAMBIS EXCHANGE', style: 'header' },
            { text: 'Relevé de compte client', style: 'subheader' },
            {
                columns: [
                    [
                        { text: `Client : ${client.value.nom ?? ''} ${client.value.prenom ?? ''}` },
                        { text: `Compte : ${account.code ?? ''} — ${account.currency?.code ?? ''}` },
                    ],
                    [
                        { text: periode, alignment: 'right' },
                        { text: `Généré le ${new Date().toLocaleString('fr-FR')}`, alignment: 'right' },
                    ],
                ],
                margin: [0, 10, 0, 15],
            },
            {
                table: {
                    headerRows: 1,
                    widths: ['auto', 'auto', '*', 'auto', 'auto', 'auto'],
                    body,
                },
                layout: {
                    fillColor: (rowIndex) => (rowIndex === 0 ? '#305496' : (rowIndex % 2 === 0 ? '#f9fafb' : null)),
                },
            },
        ],
        styles: {
            header: { fontSize: 16, bold: true, color: '#305496' },
            subheader: { fontSize: 11, margin: [0, 2, 0, 0] },
            tableHeader: { bold: true, color: 'white', fontSize: 10 },
        },
        defaultStyle: { fontSize: 9 },
    };

    const clientName = `${client.value.nom ?? ''}_${client.value.prenom ?? ''}`.trim() || 'client';
    pdfMake.createPdf(docDefinition).download(`Releve_${clientName}_${account.code ?? ''}.pdf`);
}

watch(selectedAccountId, () => {
    filters.value.from = '';
    filters.value.to = '';
    fetchHistory();
});

onMounted(async () => {
    await fetchClient();
    if (selectedAccountId.value) {
        fetchHistory();
    }
});

</script>

<style scoped>
.table-responsive {
    overflow-x: auto;
}
</style>
