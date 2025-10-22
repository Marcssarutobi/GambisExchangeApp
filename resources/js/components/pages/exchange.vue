<template>
    <main>

        <!-- Page Title Start -->
        <div class="flex items-center md:justify-between flex-wrap gap-2 mb-5">
            <h4 class="text-default-900 text-lg font-semibold">Exchanges list</h4>

            <div class="md:flex hidden items-center gap-3 text-sm font-semibold">
                <RouterLink to="/" class="text-sm font-medium text-default-700">Home</RouterLink>

                <i class="i-tabler-chevron-right text-lg flex-shrink-0 text-default-500 rtl:rotate-180"></i>

                <RouterLink to="/customer" class="text-sm font-medium text-default-700" aria-current="page">Exchanges list</RouterLink>
            </div>
        </div>
        <!-- Page Title End -->

        <div class="col-lg-12 mt-8">
            <div class="card overflow-hidden p-3">
                <div class="card-header text-end">
                    <button type="button" @click="showModal = true" class="btn btn-lg bg-primary text-white">Add Exchanges</button>
                </div>
                <div class="overflow-x-auto">
                    <div class="min-w-full inline-block align-middle">
                        <div class="overflow-hidden">
                            <DataTable :data="allMovements" :columns="columns" />
                        </div>
                    </div>
                </div>
                
            </div> <!-- end card -->
        </div>

        <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center" style="z-index: 1000;">
            <div class="bg-white rounded-lg p-6 w-full sm:w-3/4 md:w-2/3 lg:w-1/2 max-h-[90vh] lg:max-w-[50%] overflow-y-auto">
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

    </main>
</template>
<script setup>

    import { computed, onMounted, ref, render, watch } from 'vue';
    import DataTable from '../layout/Datatable.vue';
    import { deleteData, getData, getSingleData, postData, putData } from '../plugins/api';
    import Swal from 'sweetalert2';

    const allMovements = ref([]);
    const allAccount = ref([]);
    const allCurrency = ref([]);
    const allExchange = ref([]);
    const data = ref({
        account_id: '',
        type: '',
        amount: '',
        rate: '',
        final_amount: '',
        currency_id: '',
        performed_by:''
    });
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
            title: 'Type',
            data: 'type',
            render: (data, type, row) => {
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
                return `${row.rate} ${row.account?.currency?.code}`;
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
                return `${value} ${currency}`;
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
                return `${value} ${currency}`;
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
            if (field === 'rate') continue;
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
                        final_amount: '',
                        currency_id: ''
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

    onMounted(() => {
        AllMovements();
        AllAccount()
        AllCurrencyFunction()
        AllExchangeRate()
    });

    watch(
        [() => data.value.amount, () => data.value.rate],
        ([amount, rate]) => {
            if (amount && rate) {
                // Calcul automatique du montant final
                data.value.final_amount = (parseFloat(amount) * parseFloat(rate)).toFixed(2)
            } else {
                data.value.final_amount = amount
            }
        }
    )

</script>
<style scoped>
    
</style>