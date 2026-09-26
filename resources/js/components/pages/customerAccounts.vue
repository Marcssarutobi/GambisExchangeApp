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

        <div class="mb-4">
            <RouterLink :to="`/customer/${clientId}/accounts`" class="inline-flex items-center gap-2 text-sm font-medium text-primary hover:underline">
                ← Retour aux comptes
            </RouterLink>
        </div>

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
                    class="btn bg-red-600 text-white ms-auto disabled:opacity-50">
                    <i class="material-symbols-rounded align-middle">picture_as_pdf</i> Exporter en PDF
                </button>

                <button type="button" @click="exportToExcel" :disabled="!movements.length"
                    class="btn bg-green-600 text-white disabled:opacity-50">
                    <i class="material-symbols-rounded align-middle">table_view</i> Exporter en Excel
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
                                <div>{{ mvt.performed_by ?? (mvt.type === 'deposit' ? 'Dépôt' : 'Retrait') }}</div>
                                <div v-if="transferLabel(mvt)" class="text-xs" style="color:#2563eb;">{{ transferLabel(mvt) }}</div>
                                <div v-if="transferConversion(mvt, accountCurrency)" class="text-xs text-gray-500">{{ transferConversion(mvt, accountCurrency) }}</div>
                            </td>
                            <td class="px-4 py-2 text-right">
                                <span v-if="mvt.type === 'withdraw'" class="text-red-600 font-semibold">
                                    {{ formatAmount(mvt.final_amount) }} {{ accountCurrency }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-right">
                                <span v-if="mvt.type === 'deposit'" class="text-green-600 font-semibold">
                                    {{ formatAmount(mvt.final_amount) }} {{ accountCurrency }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-right font-semibold"
                                :class="mvt.balance_after >= 0 ? 'text-blue-600' : 'text-red-600'">
                                {{ formatAmount(mvt.balance_after) }} {{ accountCurrency }}
                            </td>
                        </tr>

                        <tr v-if="movements.length" class="bg-gray-100 font-semibold">
                            <td class="px-4 py-2" colspan="5">Solde d'ouverture de la période</td>
                            <td class="px-4 py-2 text-right"
                                :class="openingBalance >= 0 ? 'text-blue-600' : 'text-red-600'">
                                {{ formatAmount(openingBalance) }} {{ accountCurrency }}
                            </td>
                        </tr>

                        <!-- Totaux calculés sur la période affichée : Débit, Crédit et Solde de clôture -->
                        <tr v-if="movements.length" class="bg-gray-100 font-semibold border-t border-gray-300">
                            <td class="px-4 py-2" colspan="3">Total de la période</td>
                            <td class="px-4 py-2 text-right text-red-600">
                                {{ formatAmount(periodTotals.debit) }} {{ accountCurrency }}
                            </td>
                            <td class="px-4 py-2 text-right text-green-600">
                                {{ formatAmount(periodTotals.credit) }} {{ accountCurrency }}
                            </td>
                            <td class="px-4 py-2 text-right"
                                :class="periodTotals.closing >= 0 ? 'text-blue-600' : 'text-red-600'">
                                {{ formatAmount(periodTotals.closing) }} {{ accountCurrency }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div> <!-- end card -->

    </main>
</template>

<script setup>

import { computed, onMounted, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import { getData, getSingleData } from '../plugins/api';
import { transferLabel, transferConversion } from '../plugins/transfer';
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

// Devise du compte sélectionné (ex: XOF, NRN)
const accountCurrency = computed(() => {
    const account = accounts.value.find(acc => acc.id === selectedAccountId.value);
    return account?.currency?.code ?? '';
});

// Totaux de la période affichée (recalculés à chaque changement de filtre) :
//   Débit  = somme des retraits, Crédit = somme des dépôts,
//   Solde  = solde d'ouverture + crédit - débit (solde de clôture)
const round2 = (n) => Math.round(n * 100) / 100;
const periodTotals = computed(() => {
    let debit = 0;
    let credit = 0;
    for (const m of movements.value) {
        const amount = Number(m.final_amount) || 0;
        if (m.type === 'withdraw') debit += amount;
        else if (m.type === 'deposit') credit += amount;
    }
    debit = round2(debit);
    credit = round2(credit);
    return { debit, credit, closing: round2(Number(openingBalance.value || 0) + credit - debit) };
});

const loadingAccounts = ref(false);
const loadingHistory = ref(false);

const filters = ref({
    from: '',
    to: '',
});

function formatAmount(value) {
    return Number(value ?? 0).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// pdfmake ne sait pas afficher l'espace fine insécable utilisée par toLocaleString('fr-FR') comme
// séparateur de milliers (elle s'affiche comme un symbole de remplacement, une sorte de croix, à la
// place de l'espace) : on la remplace par un espace normal, uniquement pour le PDF.
// L'affichage à l'écran (formatAmount) n'est pas concerné, il s'affiche déjà correctement.
function formatAmountForPdf(value) {
    return formatAmount(value).replace(/[\u202F\u00A0]/g, ' ');
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

        const queryAccountId = route.query.account ? Number(route.query.account) : null;
        const matchesAnAccount = queryAccountId && accounts.value.some(acc => acc.id === queryAccountId);

        if (matchesAnAccount) {
            selectedAccountId.value = queryAccountId;
        } else if (accounts.value.length) {
            selectedAccountId.value = accounts.value[0].id;
        }
    } catch (error) {
        console.error('Erreur lors du chargement du client :', error);
    } finally {
        loadingAccounts.value = false;
    }
}

// Évite qu'une réponse plus lente écrase une plus récente quand on change les dates rapidement
let historyRequestId = 0;

async function fetchHistory() {
    if (!selectedAccountId.value) return;

    const requestId = ++historyRequestId;
    loadingHistory.value = true;
    try {
        const params = new URLSearchParams();
        if (filters.value.from) params.append('from', filters.value.from);
        if (filters.value.to) params.append('to', filters.value.to);

        const query = params.toString() ? `?${params.toString()}` : '';
        const res = await getData(`/movements/account/${selectedAccountId.value}${query}`);

        if (requestId !== historyRequestId) return; // une requête plus récente est en cours
        movements.value = res.data.data.movements;
        openingBalance.value = res.data.data.opening_balance;
    } catch (error) {
        console.error("Erreur lors du chargement de l'historique :", error);
    } finally {
        if (requestId === historyRequestId) loadingHistory.value = false;
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
            {
                stack: [
                    { text: mvt.performed_by ?? (mvt.type === 'deposit' ? 'Dépôt' : 'Retrait') },
                    ...(transferLabel(mvt) ? [{ text: transferLabel(mvt), fontSize: 8, color: '#2563eb' }] : []),
                    ...(transferConversion(mvt, accountCurrency.value) ? [{ text: transferConversion(mvt, accountCurrency.value), fontSize: 8, color: '#6b7280' }] : []),
                ],
            },
            {
                text: mvt.type === 'withdraw' ? `${formatAmountForPdf(mvt.final_amount)} ${accountCurrency.value}` : '',
                color: 'red',
                bold: true,
                alignment: 'right',
            },
            {
                text: mvt.type === 'deposit' ? `${formatAmountForPdf(mvt.final_amount)} ${accountCurrency.value}` : '',
                color: 'green',
                bold: true,
                alignment: 'right',
            },
            {
                text: `${formatAmountForPdf(mvt.balance_after)} ${accountCurrency.value}`,
                color: mvt.balance_after >= 0 ? '#2563eb' : 'red',
                bold: true,
                alignment: 'right',
            },
        ]);
    });

    body.push([
        { text: "Solde d'ouverture de la période", colSpan: 5, bold: true },
        {}, {}, {}, {},
        {
            text: `${formatAmountForPdf(openingBalance.value)} ${accountCurrency.value}`,
            bold: true,
            alignment: 'right',
            color: openingBalance.value >= 0 ? '#2563eb' : 'red',
        },
    ]);

    body.push([
        { text: 'Total de la période', colSpan: 3, bold: true },
        {}, {},
        { text: `${formatAmountForPdf(periodTotals.value.debit)} ${accountCurrency.value}`, bold: true, alignment: 'right', color: 'red' },
        { text: `${formatAmountForPdf(periodTotals.value.credit)} ${accountCurrency.value}`, bold: true, alignment: 'right', color: 'green' },
        {
            text: `${formatAmountForPdf(periodTotals.value.closing)} ${accountCurrency.value}`,
            bold: true,
            alignment: 'right',
            color: periodTotals.value.closing >= 0 ? '#2563eb' : 'red',
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

// Export Excel : réutilise l'endpoint déjà utilisé ailleurs dans l'application (même mise en
// forme, même tri du plus ancien au plus récent), filtré sur ce compte et la période affichée.
function exportToExcel() {
    if (!selectedAccountId.value) return;
    const params = new URLSearchParams({ account_id: selectedAccountId.value });
    if (filters.value.from) params.append('from', filters.value.from);
    if (filters.value.to) params.append('to', filters.value.to);
    window.open(`/api/export-history?${params.toString()}`, '_blank');
}

// Filtre automatique : le tableau se met à jour dès qu'une date change (plus besoin de cliquer sur « Filtrer »)
watch(() => [filters.value.from, filters.value.to], () => {
    fetchHistory();
});

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
