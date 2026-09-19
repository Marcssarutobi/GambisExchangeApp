<template>
    <main>

        <!-- Page Title Start -->
        <div class="flex items-center md:justify-between flex-wrap gap-2 mb-5">
            <h4 class="text-default-900 text-lg font-semibold">Exchanges list</h4>

            <div class="md:flex hidden items-center gap-3 text-sm font-semibold">
                <RouterLink to="/" class="text-sm font-medium text-default-700">Home</RouterLink>

                <i class="material-symbols-rounded text-lg flex-shrink-0 text-default-500 rtl:rotate-180">chevron_right</i>

                <RouterLink to="/customer" class="text-sm font-medium text-default-700" aria-current="page">Exchanges list</RouterLink>
            </div>
        </div>
        <!-- Page Title End -->

        <div class="col-lg-12 mt-8">
            <div class="card overflow-hidden p-3">
                <div class="card-header text-end">
                    <button type="button" @click="openTransferModal" class="btn btn-lg bg-white text-dark rounded-md shadow-sm me-2"><i class="material-symbols-rounded me-1">sync_alt</i> Transfer</button>
                    <button type="button" @click="showModal = true" class="btn btn-lg bg-primary text-white rounded-md shadow-sm"><i class="material-symbols-rounded me-1">swap_horiz</i> Add Exchanges</button>
                </div>
                <div class="overflow-x-auto">
                    <div class="min-w-full inline-block align-middle">
                        <div class="overflow-hidden">
                            <DataTable :data="allMovements" :columns="columns" :DeleteAllFunction="() => {}" />
                        </div>
                    </div>
                </div>
                
            </div> <!-- end card -->
        </div>

        <Teleport to="body">
        <div v-if="showModal" class="modal-overlay" style="position:fixed; top:0; right:0; bottom:0; left:0; z-index:1000; background:rgba(0,0,0,0.6); display:flex; align-items:center; justify-content:center; padding:1rem;">
            <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 p-6 overflow-y-auto" style="width:100%; max-width:576px; max-height:85vh;">
                <h2 class="text-lg font-semibold">Add movements</h2>


                <form class="mt-3 space-y-4" @submit.prevent="AddMovementFunction">

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="">
                            <label class="block text-sm font-medium text-gray-700">Accounts</label>
                            <select name="account_id" id="account_id" v-model="data.account_id" class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                                <option value="">Select Account</option>
                                <option v-for="account in allAccount" :key="account.id" :value="account.id">{{ account.client?.nom }} {{ account.client?.prenom }} ({{ account.currency?.code }})</option>
                            </select>
                            <span v-if="isEmpty.account_id" class="text-danger">{{ msgInput.account_id }}</span>
                        </div>
    
                        <div class="">
                            <label class="block text-sm font-medium text-gray-700">Currency</label>
                            <select name="currency_id" id="currency_id" v-model="data.currency_id" class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                                <option value="">Select Currency</option>
                                <option v-for="currency in allCurrency" :key="currency.id" :value="currency.id">{{ currency.name }}</option>
                            </select>
                            <span v-if="isEmpty.currency_id" class="text-danger">{{ msgInput.currency_id }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="">
                            <label class="block text-sm font-medium text-gray-700">Type</label>
                            <select name="type" id="type" v-model="data.type" class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                                <option value="">Select Type</option>
                                <option value="deposit">Deposit</option>
                                <option value="withdraw">Withdrawal</option>
                            </select>
                            <span v-if="isEmpty.type" class="text-danger">{{ msgInput.type }}</span>
                        </div>
                        <div class="">
                            <label class="block text-sm font-medium text-gray-700">Amount</label>
                            <input type="text" class="mt-1 block w-full border border-gray-300 rounded-md p-2" :class="{'border border-red-500':isEmpty.amount}" placeholder="Enter Amount" v-model="data.amount">
                            <span v-if="isEmpty.amount" class="text-danger">{{ msgInput.amount }}</span>
                        </div>
                     </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="">
                            <label class="block text-sm font-medium text-gray-700">Rate</label>
                            <input type="number" min="0"   step="0.01" v-model="data.rate" class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                        </div>
                        <div class="">
                            <label class="block text-sm font-medium text-gray-700">Final Amount</label>
                            <input  disabled type="text" class="mt-1 block w-full border border-gray-300 rounded-md p-2" :class="{'border border-red-500':isEmpty.final_amount}" placeholder="Final Amount" v-model="data.final_amount">
                            <span v-if="isEmpty.final_amount" class="text-danger">{{ msgInput.final_amount }}</span>
                        </div>
                    </div>

                    <!--
                        Point 5 : correctif du bug de calcul. Le taux reste saisi manuellement
                        (il varie), mais l'agent choisit désormais explicitement le sens à
                        appliquer, au lieu d'une multiplication systématique (bug signalé sur le Naira).
                        Toujours visible (ne dépend plus d'une détection auto compte/devise, trop
                        fragile) : ignoré côté serveur si la devise saisie = devise du compte.
                    -->
                    <div class="grid grid-cols-1 gap-4">
                        <div class="">
                            <label class="block text-sm font-medium text-gray-700">Sens du taux</label>
                            <select v-model="data.rate_direction" class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                                <option value="multiply">Multiplier (montant × taux)</option>
                                <option value="divide">Diviser (montant ÷ taux)</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Utilisé uniquement si la devise saisie est différente de la devise du compte.</p>
                        </div>
                    </div>

                    <div class="">
                        <label class="block text-sm font-medium text-gray-700">Performed By</label>
                        <input type="text" class="mt-1 block w-full border border-gray-300 rounded-md p-2" :class="{'border border-red-500':isEmpty.performed_by}" placeholder="Enter Performed By" v-model="data.performed_by">
                        <span v-if="isEmpty.performed_by" class="text-danger">{{ msgInput.performed_by }}</span>
                    </div>

                    <div class="mt-4 flex justify-end gap-2">
                        <button class="px-4 py-2 bg-gray-200 rounded" @click="showModal = false">
                            Close
                        </button>
                        <button disabled v-if="isLoader" class="px-4 py-2 bg-blue-600 text-white rounded">
                            <div class="spinner-border text-light" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </button>
                        <button v-else type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">
                            Save
                        </button>
                    </div>

                </form>
            </div>
        </div>
        </Teleport>

        <!-- Transfert de compte à compte -->
        <Teleport to="body">
        <div v-if="showTransferModal" class="modal-overlay" style="position:fixed; top:0; right:0; bottom:0; left:0; z-index:1000; background:rgba(0,0,0,0.6); display:flex; align-items:center; justify-content:center; padding:1rem;">
            <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 p-6 overflow-y-auto" style="width:100%; max-width:576px; max-height:85vh;">
                <h2 class="text-lg font-semibold">Transfert entre comptes</h2>

                <form class="mt-3 space-y-4" @submit.prevent="submitTransfer">

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Compte source (débité)</label>
                        <select v-model="transfer.from_account_id" class="mt-1 block w-full border border-gray-300 rounded-md p-2" :class="{'border-red-500': transferErrors.from_account_id}">
                            <option value="">Select Account</option>
                            <option v-for="account in allAccount" :key="account.id" :value="account.id">
                                {{ account.code }} — {{ account.client?.nom }} {{ account.client?.prenom }} ({{ account.currency?.code }})
                            </option>
                        </select>
                        <p v-if="fromAccount" class="text-xs text-gray-500 mt-1">
                            Solde disponible :
                            <span class="font-semibold" style="color:#2563eb;">{{ formatMoney(fromAccount.balance) }} {{ fromAccount.currency?.code }}</span>
                        </p>
                        <span v-if="transferErrors.from_account_id" class="text-danger">{{ transferErrors.from_account_id }}</span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Compte destination (crédité)</label>
                        <select v-model="transfer.to_account_id" class="mt-1 block w-full border border-gray-300 rounded-md p-2" :class="{'border-red-500': transferErrors.to_account_id}">
                            <option value="">Select Account</option>
                            <option v-for="account in destinationAccounts" :key="account.id" :value="account.id">
                                {{ account.code }} — {{ account.client?.nom }} {{ account.client?.prenom }} ({{ account.currency?.code }})
                            </option>
                        </select>
                        <span v-if="transferErrors.to_account_id" class="text-danger">{{ transferErrors.to_account_id }}</span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Montant<span v-if="fromAccount"> ({{ fromAccount.currency?.code }})</span>
                        </label>
                        <input type="text" inputmode="decimal" v-model="transfer.amount" placeholder="Enter Amount" class="mt-1 block w-full border border-gray-300 rounded-md p-2" :class="{'border-red-500': transferErrors.amount}">
                        <span v-if="transferErrors.amount" class="text-danger">{{ transferErrors.amount }}</span>
                    </div>

                    <!-- Conversion : uniquement si les devises des deux comptes diffèrent -->
                    <div v-if="needsConversion" class="rounded-lg border border-gray-200 bg-gray-50 p-3 space-y-3">
                        <p class="text-xs text-gray-600">
                            Les comptes n'ont pas la même devise ({{ fromAccount?.currency?.code }} → {{ toAccount?.currency?.code }}) : indique le taux à appliquer.
                        </p>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Rate</label>
                                <input type="text" inputmode="decimal" v-model="transfer.rate" placeholder="ex: 2,35" class="mt-1 block w-full border border-gray-300 rounded-md p-2" :class="{'border-red-500': transferErrors.rate}">
                                <span v-if="transferErrors.rate" class="text-danger">{{ transferErrors.rate }}</span>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Sens du taux</label>
                                <select v-model="transfer.rate_direction" class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                                    <option value="multiply">Multiplier (montant × taux)</option>
                                    <option value="divide">Diviser (montant ÷ taux)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div v-if="transferPreview !== null" class="rounded-lg p-3 text-sm" style="background:#eff6ff;">
                        Le compte destination recevra :
                        <span class="font-semibold" style="color:#2563eb;">{{ formatMoney(transferPreview) }} {{ toAccount?.currency?.code }}</span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Performed By</label>
                        <input type="text" v-model="transfer.performed_by" placeholder="Enter Performed By" class="mt-1 block w-full border border-gray-300 rounded-md p-2" :class="{'border-red-500': transferErrors.performed_by}">
                        <span v-if="transferErrors.performed_by" class="text-danger">{{ transferErrors.performed_by }}</span>
                    </div>

                    <div class="mt-4 flex justify-end gap-2">
                        <button type="button" class="px-4 py-2 bg-gray-200 rounded" @click="showTransferModal = false">Close</button>
                        <button disabled v-if="isTransferLoader" class="px-4 py-2 bg-blue-600 text-white rounded">
                            <div class="spinner-border text-light" role="status"><span class="visually-hidden">Loading...</span></div>
                        </button>
                        <button v-else type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Transférer</button>
                    </div>
                </form>
            </div>
        </div>
        </Teleport>

    </main>
</template>
<script setup>

    import { computed, onMounted, ref, render, watch } from 'vue';
    import { useRoute } from 'vue-router';
    import DataTable from '../layout/Datatable.vue';
    import { deleteData, getData, getSingleData, postData, putData } from '../plugins/api';
    import Swal from 'sweetalert2';
    import { rateWithDirection, transferConversion } from '../plugins/transfer';

    const route = useRoute();

    const allMovements = ref([]);
    const allAccount = ref([]);
    const allCurrency = ref([]);
    const allExchange = ref([]);
    const data = ref({
        account_id: '',
        type: '',
        amount: '',
        rate: '',
        rate_direction: 'multiply',
        final_amount: '',
        currency_id: '',
        performed_by:''
    });

    // Point 2 : bouton Créditer/Débiter d'un compte -> pré-remplissage du formulaire
    // via les query params (account_id, type) au lieu de rechercher le compte dans la liste.
    function prefillFromQuery() {
        if (route.query.account_id) {
            data.value.account_id = Number(route.query.account_id);
            if (route.query.type === 'deposit' || route.query.type === 'withdraw') {
                data.value.type = route.query.type;
            }
            const account = allAccount.value.find(a => a.id === data.value.account_id);
            if (account) {
                data.value.currency_id = account.currency_id;
            }
            showModal.value = true;
        }
    }

    const isEmpty = ref({})
    const msgInput = ref({})
    const isLoader = ref(false)
    const getMovement = ref({})

    const showModal = ref(false)
    const updateModal = ref(false)

    async function AllMovements(){
        await getData('/movements').then(res => {
            allMovements.value = res.data.data;
        });
    }

    async function AllAccount() {
        try {
            await getData('/accounts').then(res=>{
                allAccount.value = res.data.data
            })
        } catch (error) {
            console.error("Error fetching accounts:", error);
        }
    }

    async function AllCurrencyFunction() {
        try {
            await getData('/currencies').then(res=>{
                allCurrency.value = res.data.data
            })
        } catch (error) {
            console.error("Error fetching currencies:", error);
        }
    }

    async function AllExchangeRate() {
        try {
            await getData('/exchangerates').then(res=>{
                allExchange.value = res.data.data
            })
        } catch (error) {
            console.error("Error fetching customers:", error);
        }
    }

    // Filtrer les taux selon la devise choisie
    const filteredExchange = computed(() => {
        if (!data.value.currency_id) {
            // Si aucune devise choisie => retourner toute la liste
            return allExchange.value
        }
        // Sinon filtrer par currency_id
        return allExchange.value.filter(rate => 
            rate.from_currency.id === data.value.currency_id
        )
    })

    // Échappe le HTML (les noms de clients sont injectés dans du HTML par DataTable)
    const escapeHtml = (str) => String(str ?? '')
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#39;');

    // ---------- Transfert de compte à compte ----------
    const emptyTransfer = () => ({
        from_account_id: '',
        to_account_id: '',
        amount: '',
        rate: '',
        rate_direction: 'multiply',
        performed_by: ''
    })
    const showTransferModal = ref(false)
    const isTransferLoader = ref(false)
    const transfer = ref(emptyTransfer())
    const transferErrors = ref({})

    // Accepte "2,35" comme "2.35" (les claviers français saisissent une virgule)
    const toNumber = (value) => parseFloat(String(value ?? '').replace(/\s/g, '').replace(',', '.'))
    const formatMoney = (value) => Number(value ?? 0).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })

    const fromAccount = computed(() => allAccount.value.find(a => a.id === transfer.value.from_account_id))
    const toAccount = computed(() => allAccount.value.find(a => a.id === transfer.value.to_account_id))
    const destinationAccounts = computed(() => allAccount.value.filter(a => a.id !== transfer.value.from_account_id))
    const needsConversion = computed(() =>
        !!fromAccount.value && !!toAccount.value && fromAccount.value.currency_id !== toAccount.value.currency_id
    )

    // Montant que recevra le compte destination (aperçu, le calcul officiel est fait côté serveur)
    const transferPreview = computed(() => {
        const amount = toNumber(transfer.value.amount)
        if (!fromAccount.value || !toAccount.value || !(amount > 0)) return null
        if (!needsConversion.value) return amount
        const rate = toNumber(transfer.value.rate)
        if (!(rate > 0)) return null
        return transfer.value.rate_direction === 'divide' ? amount / rate : amount * rate
    })

    // Si l'utilisateur change de compte source et que la destination est identique, on la vide
    watch(() => transfer.value.from_account_id, (id) => {
        if (id && transfer.value.to_account_id === id) transfer.value.to_account_id = ''
    })

    function openTransferModal() {
        transfer.value = emptyTransfer()
        transferErrors.value = {}
        showTransferModal.value = true
    }

    async function submitTransfer() {
        const t = transfer.value
        const errors = {}
        if (!t.from_account_id) errors.from_account_id = 'Please select the source account'
        if (!t.to_account_id) errors.to_account_id = 'Please select the destination account'
        if (!(toNumber(t.amount) > 0)) errors.amount = 'Please enter a valid amount'
        if (needsConversion.value && !(toNumber(t.rate) > 0)) errors.rate = 'Please enter the rate'
        if (!t.performed_by) errors.performed_by = 'Please enter performed by'
        transferErrors.value = errors
        if (Object.keys(errors).length) return

        isTransferLoader.value = true
        try {
            await postData('/transfers', {
                from_account_id: t.from_account_id,
                to_account_id: t.to_account_id,
                amount: toNumber(t.amount),
                rate: needsConversion.value ? toNumber(t.rate) : null,
                rate_direction: needsConversion.value ? t.rate_direction : null,
                performed_by: t.performed_by,
            })
            showTransferModal.value = false
            Swal.fire({ position: 'center', icon: 'success', text: 'Transfer performed', showConfirmButton: false, timer: 1500 })
            AllMovements()
            AllAccount() // met à jour les soldes affichés dans la modale
        } catch (err) {
            const message = err.response?.data?.message
                ?? Object.values(err.response?.data?.errors ?? {})[0]?.[0]
                ?? 'Transfer failed'
            Swal.fire({ position: 'center', icon: 'error', text: message, showConfirmButton: false, timer: 2500 })
        } finally {
            isTransferLoader.value = false
        }
    }

    const columns = [
        {
            title: `
                <input class="form-check-input" type="checkbox" id="select-all" style="width:18px; height:18px;">
            `,
            data:null,
            orderable: false,
            searchable: false,
            render: function (data, type, row) {
                return `<input class="form-check-input row-checkbox" data-id="${row.id}" type="checkbox"  style="width:18px; height:18px;">`;
            },
            width: "40px"
        },
        {
            title: 'Accounts',
            data: 'account.code',
            render: (data, type, row) => {
                if (!row.account) return "";
                return `<span style="font-weight: bold;">${row.account.code}</span>`;
            }
        },
        {
            title: 'Client',
            data: 'account.client.nom',
            render: (data, type, row) => {
                const client = row.account?.client;
                if (!client) return "";
                return escapeHtml(`${client.nom ?? ''} ${client.prenom ?? ''}`.trim());
            }
        },
        {
            title: 'Type',
            data: 'type',
            render: (data, type, row) => {
                if (row.transfer_ref) {
                    const other = row.counterpart_account;
                    const otherName = other?.client ? `${other.client.nom ?? ''} ${other.client.prenom ?? ''}`.trim() : '';
                    const otherLabel = escapeHtml([other?.code, otherName].filter(Boolean).join(' — '));
                    // Détail de la conversion (devises, taux, × ou ÷) quand les comptes ont des devises différentes
                    const conversion = transferConversion(row, row.account?.currency?.code);
                    const conversionHtml = conversion ? `<br><small style="color:#6b7280;">${escapeHtml(conversion)}</small>` : '';
                    return row.type === 'withdraw'
                        ? `<span class="badge text-white p-1 rounded" style="background:#2563eb;">Transfer out</span><br><small>→ ${otherLabel}</small>${conversionHtml}`
                        : `<span class="badge text-white p-1 rounded" style="background:#0891b2;">Transfer in</span><br><small>← ${otherLabel}</small>${conversionHtml}`;
                }
                if (row.type === 'deposit') {
                    return `<span class="badge bg-success text-white p-1 rounded">Deposit</span>`;
                } else if (row.type === 'withdraw') {
                    return `<span class="badge bg-danger text-white p-1 rounded">Withdrawal</span>`;
                } else {
                    return row.type;
                }
            }
        },
        {
            title: 'Amount',
            data: 'amount',
            render: (data, type, row) => {
                if (!row.amount) return "";
                return `${Number(row.amount).toLocaleString("fr-FR")} ${row.currency?.code}`;
            }
        },
        {
            title: 'Rate',
            data: 'rate',
            render: (data, type, row) => {
                if (!row.rate) return "";
                // Taux sans zéros inutiles, avec son sens : "× 2,35" ou "÷ 2,35"
                return escapeHtml(rateWithDirection(row));
            }
        },
        {
            title: 'Final Amount',
            data: 'final_amount',
            render: (data, type, row) => {
                if (!row.final_amount) return "";
                return `${Number(row.final_amount).toLocaleString("fr-FR")} ${row.account?.currency?.code}`;
            }
        },
        {
            title: 'Balance Before',
            data: 'balance_before',
            render: (data, type, row) => {
                if (!row.balance_before) return "";
                const value = Number(row.balance_before).toLocaleString("fr-FR");
                const currency = row.account?.currency?.code ?? '';
                // Si le montant est négatif, mettre en rouge
                if (row.balance_before < 0) {
                    return `<span style="color:red;font-weight:bold">${value} ${currency}</span>`;
                }
                return `<span style="color:#2563eb;font-weight:bold">${value} ${currency}</span>`;
            }
        },
        {
            title: 'Balance After',
            data: 'balance_after',
            render: (data, type, row) => {
                if (!row.balance_after) return "";
                const value = Number(row.balance_after).toLocaleString("fr-FR");
                const currency = row.account?.currency?.code ?? '';
                if (row.balance_after < 0) {
                    return `<span style="color:red;font-weight:bold">${value} ${currency}</span>`;
                }
                return `<span style="color:#2563eb;font-weight:bold">${value} ${currency}</span>`;
            }
        },
        {
            title: 'Performed By',
            data: 'performed_by',
        },
        {
            title: 'Created At',
            data: 'created_at',
            render: (data, type, row) => {
                const date = new Date(row.created_at);
                return new Intl.DateTimeFormat('fr-FR', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                }).format(date);
            }
        },
    ];

    async function AddMovementFunction() {
        
        for (const field in data.value) {
            if (field === 'rate' || field === 'rate_direction') continue;
            isEmpty.value[field] = !data.value[field]
            msgInput.value[field] = `Please enter ${field.replace('_', ' ')}`;
        }
        const allEmpty = Object.values(isEmpty.value).every(value => value === false)
        if (allEmpty) {
            isLoader.value = true
            await postData('/addmovements', data.value).then(res=>{
                if (res.status === 200) {
                    isLoader.value = false
                    showModal.value = false
                    Swal.fire({
                        position: "center",
                        icon: "success",
                        text: "Add performed",
                        showConfirmButton: false,
                        timer: 1500
                    })
                    data.value = {
                        account_id: '',
                        type: '',
                        amount: '',
                        rate: '',
                        rate_direction: 'multiply',
                        final_amount: '',
                        currency_id: '',
                        performed_by: ''
                    }
                    AllMovements();
                }
            }).catch(err =>{
                isLoader.value = false
                if (err.response.status === 400) {
                    Swal.fire({
                        position: "center",
                        icon: "error",
                        text: err.response.data.message,
                        showConfirmButton: false,
                        timer: 1500
                    }) 
                }
            })
        }

    }

    onMounted(async () => {
        AllMovements();
        await AllAccount()
        AllCurrencyFunction()
        AllExchangeRate()
        prefillFromQuery()
    });

    // Point 5 : le calcul respecte désormais le sens choisi par l'agent (multiplier ou diviser),
    // au lieu d'appliquer systématiquement une multiplication.
    watch(
        [() => data.value.amount, () => data.value.rate, () => data.value.rate_direction],
        ([amount, rate, direction]) => {
            if (amount && rate) {
                const a = parseFloat(amount)
                const r = parseFloat(rate)
                data.value.final_amount = direction === 'divide'
                    ? (a / r).toFixed(2)
                    : (a * r).toFixed(2)
            } else {
                data.value.final_amount = amount
            }
        }
    )

</script>
<style scoped>
    
</style>