<template>
    <main>

        <!-- Page Title Start -->
        <div class="flex items-center md:justify-between flex-wrap gap-2 mb-5">
            <h4 class="text-default-900 text-lg font-semibold">Accounts list</h4>

            <div class="md:flex hidden items-center gap-3 text-sm font-semibold">
                <RouterLink to="/" class="text-sm font-medium text-default-700">Home</RouterLink>

                <i class="i-tabler-chevron-right text-lg flex-shrink-0 text-default-500 rtl:rotate-180"></i>

                <RouterLink to="/customer" class="text-sm font-medium text-default-700" aria-current="page">Accounts list</RouterLink>
            </div>
        </div>
        <!-- Page Title End -->

        <div class="col-lg-12 mt-8">
            <div class="card overflow-hidden p-3">
                <div class="card-header text-end">
                    <button type="button" @click="showModal = true" class="btn btn-lg bg-primary text-white">Add Accounts</button>
                </div>
                <div class="overflow-x-auto">
                    <div class="min-w-full inline-block align-middle">
                        <div class="overflow-hidden">
                            <DataTable :data="allAccount" :columns="columns" />
                        </div>
                    </div>
                </div>
                
            </div> <!-- end card -->
        </div>

        <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center" style="z-index: 1000;">
            <div class="bg-white rounded-lg p-6 w-1/3">
                <h2 class="text-lg font-semibold">Add a Accounts</h2>


                <form class="mt-3 space-y-4" @submit.prevent="AddAccountFunction">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="">
                            <label class="block text-sm font-medium text-gray-700">Customer</label>
                            <select name="client_id" id="client_id" v-model="data.client_id" class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                                <option value="">Select Client</option>
                                <option v-for="client in allClients" :key="client.id" :value="client.id">{{ client.nom }} {{ client.prenom }}</option>
                            </select>
                            <span v-if="isEmpty.client_id" class="text-danger">{{ msgInput.client_id }}</span>
                        </div>
    
                        <div class="">
                            <label class="block text-sm font-medium text-gray-700">Currency</label>
                            <select name="currency_id" id="currency_id" v-model="data.currency_id" class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                                <option value="">Select Currency</option>
                                <option v-for="currency in allCurrency" :key="currency.id" :value="currency.id">{{ currency.code }}</option>
                            </select>
                            <span v-if="isEmpty.currency_id" class="text-danger">{{ msgInput.currency_id }}</span>
                        </div>
                    </div>
                    <div class="">
                        <label class="block text-sm font-medium text-gray-700">Balance</label>
                        <input type="text" class="mt-1 block w-full border border-gray-300 rounded-md p-2" :class="{'border border-red-500':isEmpty.balance}" placeholder="Entrez le solde" v-model="data.balance">
                        <span v-if="isEmpty.balance" class="text-danger">{{ msgInput.balance }}</span>
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

        <div v-if="updateModal" class="fixed inset-0 bg-black/50 flex items-center justify-center" style="z-index: 1000;">
            <div class="bg-white rounded-lg p-6 w-1/3">
                <h2 class="text-lg font-semibold">Update a accounts</h2>


                <form class="mt-3 space-y-4" @submit.prevent="UpdateAccountsFunction">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="">
                            <label class="block text-sm font-medium text-gray-700">Customer</label>
                            <select name="client_id" id="client_id" v-model="getAccount.client_id" class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                                <option value="">Select Client</option>
                                <option v-for="client in allClients" :key="client.id" :value="client.id">{{ client.nom }} {{ client.prenom }}</option>
                            </select>
                            <span v-if="isEmpty.client_id" class="text-danger">{{ msgInput.client_id }}</span>
                        </div>
    
                        <div class="">
                            <label class="block text-sm font-medium text-gray-700">Currency</label>
                            <select name="currency_id" id="currency_id" v-model="getAccount.currency_id" class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                                <option value="">Select Currency</option>
                                <option v-for="currency in allCurrency" :key="currency.id" :value="currency.id">{{ currency.name }}</option>
                            </select>
                            <span v-if="isEmpty.currency_id" class="text-danger">{{ msgInput.currency_id }}</span>
                        </div>
                    </div>
                    <div class="">
                        <label class="block text-sm font-medium text-gray-700">Balance</label>
                        <input type="text" class="mt-1 block w-full border border-gray-300 rounded-md p-2" :class="{'border border-red-500':isEmpty.balance}" placeholder="Entrez le solde" v-model="getAccount.balance">
                        <span v-if="isEmpty.balance" class="text-danger">{{ msgInput.balance }}</span>
                    </div>

                    <div class="mt-4 flex justify-end gap-2">
                        <button class="px-4 py-2 bg-gray-200 rounded" @click="updateModal = false">
                            Close
                        </button>
                        <button disabled v-if="isLoader" class="px-4 py-2 bg-blue-600 text-white rounded">
                            <div class="spinner-border text-light" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </button>
                        <button v-else type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">
                            Save change
                        </button>
                    </div>

                </form>
            </div>
        </div>

        <div v-if="historyModal" class="fixed inset-0 bg-black/50 flex items-center justify-center" style="z-index: 1000;">
            <div class="bg-white rounded-lg p-6 w-1/2 max-h-[80vh] overflow-y-auto">
                <h2 class="text-lg font-semibold mb-4">History Accounts</h2>

                <!-- Accordéon -->
                <div class="border rounded-lg overflow-hidden mb-3" v-for="history in allHistory" :key="history.month">
                    <button @click="toggleAccordion(history.month)" class="w-full flex justify-between items-center px-4 py-3 bg-primary-100 hover:bg-gray-200 transition">
                        <span class="font-medium">{{ history.month }}</span>
                        <svg
                        :class="{'rotate-180': openAccordions[history.month]}"
                        class="w-5 h-5 transform transition-transform duration-300"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div v-show="openAccordions[history.month]" class="px-4 py-3 border-t bg-white text-sm text-gray-700">
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full border border-gray-200">
                                <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-2 text-left text-gray-700">Created at</th>
                                    <th class="px-4 py-2 text-left text-gray-700">Type</th>
                                    <th class="px-4 py-2 text-left text-gray-700">Amount</th>
                                    <th class="px-4 py-2 text-left text-gray-700">Rate</th>
                                    <th class="px-4 py-2 text-left text-gray-700">Final Amount</th>
                                    <th class="px-4 py-2 text-left text-gray-700">Balance after</th>
                                </tr>
                                </thead>
                                <tbody>
                                    <tr :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50'" v-for="(data,index) in history.history" :key="index">
                                        <td class="px-4 py-2">{{ formatDate(data.created_at) }}</td>
                                        <td class="px-4 py-2">{{ data.type }}</td>
                                        <td class="px-4 py-2">{{ data.amount }} {{ data.currency?.code }}</td>
                                        <td class="px-4 py-2">{{ data.rate }}</td>
                                        <td class="px-4 py-2">{{ data.final_amount }} {{ data.account?.currency?.code }}</td>
                                        <td class="px-4 py-2">{{ data.balance_after }} {{ data.account?.currency?.code }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

                <div class="mt-4 flex justify-end gap-2">
                    <button class="px-4 py-2 bg-gray-200 rounded" @click="historyModal = false">
                        Close
                    </button>
                </div>
            </div>
        </div>

    </main>
</template>
<script setup>

    import { computed, onMounted, ref } from 'vue';
    import DataTable from '../layout/Datatable.vue';
    import { deleteData, getData, getSingleData, postData, putData } from '../plugins/api';
    import Swal from 'sweetalert2';

    const allAccount = ref([]);
    const allClients = ref([]);
    const allCurrency = ref([]);
    const allHistory = ref([]);
    const data = ref({
        client_id: '',
        currency_id: '',
        balance: '',
    });
    const isEmpty = ref({})
    const msgInput = ref({})
    const isLoader = ref(false)
    const getAccount = ref({})
    
    const showModal = ref(false)
    const updateModal = ref(false)
    const historyModal = ref(false)
    const openAccordions = ref({});

    async function AllCustomer() {
        try {
            await getData('/clients').then(res=>{
                allClients.value = res.data.data
            })
        } catch (error) {
            console.error("Error fetching customers:", error);
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

    async function AllAccount() {
        try {
            await getData('/accounts').then(res=>{
                allAccount.value = res.data.data
            })
        } catch (error) {
            console.error("Error fetching accounts:", error);
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
            title: 'Code',
            data: null,
            render: (data, type, row) => {
                return `<div style="display:flex; align-items:center; justify-content: flex-start;font-weight: 600;">
                        <span class="fw-bold" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; line-height: 1.4em; max-height: 2.8em; word-break: break-word;" >${row.code}</span>
                    </div>`;
            }
        },
        {
            title: 'Client',
            data: null,
            render: function (data, type, row) {
                return `${row.client ? row.client.nom + ' ' + row.client.prenom : 'N/A'}`;
            }
        },
        {
            title: 'Balance',
            data: null,
            render: function (data, type, row) {
                if (!row.balance) return "";
                return Number(row.balance).toLocaleString("fr-FR");
            }
        },
        {
            title: 'Currency',
            data: 'currency.code', // Pas de data directe car on combine deux champs
        },
        {
            title: 'Actions',
            data: null,
            orderable: false,
            searchable: false,
            render: function (data, type, row) {
                return `
                    <button class="btn bg-white text-dark me-3" onClick="HistoryAccountFunction(${row.id})"><i class="fas fa-history"></i> History</button>
                    <button class="btn bg-primary text-white me-3" onClick="ShowAccountFunction(${row.id})"><i class="fas fa-edit"></i> Edit</button>
                    <button class="btn bg-danger text-white" onClick="DeleteAccountFunction(${row.id})"><i class="fas fa-trash"></i> Delete</button>
                `;
            }
        }
    ];

    async function HistoryAccountFunction(accountId) {
        try {
            await getData(`/movements/history/${accountId}`).then(res=>{
                console.log(res.data.data);
                allHistory.value = res.data.data;
                historyModal.value = true
            })
        } catch (error) {
            console.error("Error fetching history:", error);
        }
        
    }

    function toggleAccordion(month) {
        // Si le mois est déjà ouvert, on le ferme
        if (openAccordions.value[month]) {
            openAccordions.value[month] = false;
        } else {
            // Fermer tous les autres mois
            for (const key in openAccordions.value) {
            openAccordions.value[key] = false;
            }
            // Ouvrir le mois cliqué
            openAccordions.value[month] = true;
        }
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0'); // Mois commence à 0
        const year = date.getFullYear();
        return `${day}/${month}/${year}`;
    }

    async function AddAccountFunction() {
        for (const field in data.value) {
            isEmpty.value[field] = !data.value[field]
            msgInput.value[field] = `Please enter ${field.replace('_', ' ')}`;
        }
        const allEmpty = Object.values(isEmpty.value).every(value => value === false)
        if (allEmpty) {
            isLoader.value = true
            try {
                await postData('/addaccounts', data.value).then(res => {
                    isLoader.value = false
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Account added successfully',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    AllAccount();
                    showModal.value = false
                    data.value = {
                        client_id: '',
                        currency_id: '',
                        balance: '',
                    }
                })
            } catch (error) {
                isLoader.value = false
                if (error.response && error.response.status === 422) {
                    const errors = error.response.data.errors;
                    for (const key in errors) {
                        if (errors.hasOwnProperty(key)) {
                            msgInput.value[key] = errors[key][0];
                            isEmpty.value[key] = true;
                        }
                    }
                } else {
                    console.error("Error adding account:", error);
                }
            }
            
        }
    }

    async function ShowAccountFunction(id) {
        try {
            await getSingleData(`/accounts/${id}`).then(res => {
                getAccount.value = res.data.data
                updateModal.value = true
            })
        } catch (error) {
            console.error("Error fetching account:", error);
        }
    }

    async function UpdateAccountsFunction() {
        isLoader.value = true
        await putData('/updateaccounts/'+getAccount.value.id,getAccount.value).then(res=>{
            if (res.status === 200) {
                isLoader.value = false
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Currency updated successfully',
                    timer: 2000,
                    showConfirmButton: false,
                });
                getAccount.value = {}
                updateModal.value = false
                AllAccount()
            }
        })
    }

    async function DeleteAccountFunction(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    await deleteData(`/deleteaccounts/${id}`).then(res => {
                        if (res.status === 200) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'Account has been deleted.',
                                timer: 2000,
                                showConfirmButton: false
                            });
                            AllAccount();
                        }
                    })
                } catch (error) {
                    console.error("Error deleting account:", error);
                }
            }
        })
        
    }

    onMounted(() => {
        AllAccount();
        AllCustomer()
        AllCurrencyFunction()
        window.ShowAccountFunction = ShowAccountFunction
        window.DeleteAccountFunction = DeleteAccountFunction
        window.HistoryAccountFunction = HistoryAccountFunction
    });

</script>
<style scoped>
    
</style>