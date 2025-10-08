<template>
    <main>
        
        <!-- Page Title Start -->
        <div class="flex items-center md:justify-between flex-wrap gap-2 mb-5">
            <h4 class="text-default-900 text-lg font-semibold">Currency Purchases</h4>

            <div class="md:flex hidden items-center gap-3 text-sm font-semibold">
                <RouterLink to="/" class="text-sm font-medium text-default-700">Home</RouterLink>

                <i class="i-tabler-chevron-right text-lg flex-shrink-0 text-default-500 rtl:rotate-180"></i>

                <RouterLink to="/customer" class="text-sm font-medium text-default-700" aria-current="page">Currency Purchases</RouterLink>
            </div>
        </div>
        <!-- Page Title End -->

        <div class="grid xl:grid-cols-4 md:grid-cols-2 gap-6 mb-6">

            <div v-for="(data,index) in gain" :key="index" class="card group overflow-hidden transition-all duration-500 hover:shadow-lg hover:-translate-y-0.5">
                <div class="card-body">
                    <div class="flex items- justify-between">
                        <div>
                            <p class="text-xs tracking-wide font-semibold uppercase text-default-700 mb-3">Gain {{ data.currency }}</p>
                            <h4 class="font-semibold text-2xl"
                                :class="{
                                    'text-green-600': data.real_gain > 0,
                                    'text-red-600': data.real_gain < 0,
                                    'text-gray-800': data.real_gain == 0
                                }"
                            >
                            {{ data.real_gain }} {{ data.currency }}
                            </h4>
                        </div>
                        
                        <div
                            class="rounded-full flex justify-center items-center w-14 h-14  transition-colors duration-300"
                            :class="{
                            'bg-green-100': data.real_gain > 0,
                            'bg-red-100': data.real_gain < 0,
                            'bg-gray-200': data.real_gain == 0
                            }"
                        >
                            <i
                            class="material-symbols-rounded text-2xl"
                            :class="{
                                'text-green-600': data.real_gain > 0,
                                'text-red-600': data.real_gain < 0,
                                'text-gray-600': data.real_gain == 0
                            }"
                            >
                            {{ data.real_gain > 0 ? 'arrow_upward' : (data.real_gain < 0 ? 'arrow_downward' : 'remove') }}
                            </i>
                        </div>

                    </div>
                </div>
            </div>

        </div>

        <div class="col-lg-12 mt-8">
            <div class="card overflow-hidden p-3">
                <div class="card-header text-end">
                    <button type="button" @click="showModal = true" class="btn btn-lg bg-primary text-white">Add Purchases</button>
                </div>
                <div class="overflow-x-auto">
                    <div class="min-w-full inline-block align-middle">
                        <div class="overflow-hidden">
                            <DataTable :data="purchases" :columns="columns" />
                        </div>
                    </div>
                </div>
                
            </div> <!-- end card -->
        </div>

        <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 sm:p-6 md:p-8" style="z-index: 1000;">
            <div class="bg-white rounded-lg p-6 w-full sm:w-3/4 md:w-2/3 lg:w-1/2 max-h-[90vh] lg:max-w-[50%] overflow-y-auto">
                <h2 class="text-lg font-semibold">Add Purchases</h2>


                <form class="mt-3 space-y-4" @submit.prevent="AddCurrencyPurchase" >
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="">
                            <label class="block text-sm font-medium text-gray-700">Supplier Name</label>
                            <input type="text" class="mt-1 block w-full border border-gray-300 rounded-md p-2" :class="{'border border-red-500':isEmpty.supplier}" placeholder="Entrez le nom du fournisseur" v-model="data.supplier">
                            <span v-if="isEmpty.supplier" class="text-danger">{{ msgInput.supplier }}</span>
                        </div>
    
                        <div class="">
                            <label class="block text-sm font-medium text-gray-700">Amount Purchased</label>
                            <input type="text" class="mt-1 block w-full border border-gray-300 rounded-md p-2" :class="{'border border-red-500':isEmpty.amount_purchased}" placeholder="Entrez le montant acheté" v-model="data.amount_purchased">
                            <span v-if="isEmpty.amount_purchased" class="text-danger">{{ msgInput.amount_purchased }}</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="">
                            <label class="block text-sm font-medium text-gray-700">Currency</label>
                            <select name="currency_id" id="currency_id" v-model="data.currency_id" class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                                <option value="">Select Currency</option>
                                <option v-for="currency in allCurrency" :key="currency.id" :value="currency.id">{{ currency.name }}</option>
                            </select>
                            <span v-if="isEmpty.currency_id" class="text-danger">{{ msgInput.currency_id }}</span>
                        </div>
    
                        <div class="">
                            <label class="block text-sm font-medium text-gray-700">Rate Purchase</label>
                            <input type="text" class="mt-1 block w-full border border-gray-300 rounded-md p-2" :class="{'border border-red-500':isEmpty.rate}" placeholder="Entrez le taux" v-model="data.rate">
                            <span v-if="isEmpty.rate" class="text-danger">{{ msgInput.rate }}</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="">
                            <label class="block text-sm font-medium text-gray-700">Payment Currency</label>
                            <select name="payment_currency_id" id="payment_currency_id" v-model="data.payment_currency_id" class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                                <option value="">Select Currency</option>
                                <option v-for="currency in allCurrency" :key="currency.id" :value="currency.id">{{ currency.name }}</option>
                            </select>
                            <span v-if="isEmpty.payment_currency_id" class="text-danger">{{ msgInput.payment_currency_id }}</span>
                        </div>

                        <div class="">
                            <label class="block text-sm font-medium text-gray-700">Total Paid</label>
                            <input type="text" class="mt-1 block w-full border border-gray-300 rounded-md p-2" disabled :class="{'border border-red-500':isEmpty.total_paid}" placeholder="Entrez le total payé" v-model="data.total_paid">
                            <span v-if="isEmpty.total_paid" class="text-danger">{{ msgInput.total_paid }}</span>
                        </div>
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
            <div class="bg-white rounded-lg p-6 w-full sm:w-3/4 md:w-2/3 lg:w-1/2 max-h-[90vh] lg:max-w-[50%] overflow-y-auto">
                <h2 class="text-lg font-semibold">Update Purchases</h2>


                <form class="mt-3 space-y-4" @submit.prevent="UpdateCurrencyPurchase" >
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="">
                            <label class="block text-sm font-medium text-gray-700">Supplier Name</label>
                            <input type="text" class="mt-1 block w-full border border-gray-300 rounded-md p-2" :class="{'border border-red-500':isEmpty.supplier}" placeholder="Entrez le nom du fournisseur" v-model="getCurrencyPurchaseId.supplier">
                            <span v-if="isEmpty.supplier" class="text-danger">{{ msgInput.supplier }}</span>
                        </div>
    
                        <div class="">
                            <label class="block text-sm font-medium text-gray-700">Amount Purchased</label>
                            <input type="text" class="mt-1 block w-full border border-gray-300 rounded-md p-2" :class="{'border border-red-500':isEmpty.amount_purchased}" placeholder="Entrez le montant acheté" v-model="getCurrencyPurchaseId.amount_purchased">
                            <span v-if="isEmpty.amount_purchased" class="text-danger">{{ msgInput.amount_purchased }}</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="">
                            <label class="block text-sm font-medium text-gray-700">Currency</label>
                            <select name="currency_id" id="currency_id" v-model="getCurrencyPurchaseId.currency_id" class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                                <option value="">Select Currency</option>
                                <option v-for="currency in allCurrency" :key="currency.id" :value="currency.id">{{ currency.name }}</option>
                            </select>
                            <span v-if="isEmpty.currency_id" class="text-danger">{{ msgInput.currency_id }}</span>
                        </div>
    
                        <div class="">
                            <label class="block text-sm font-medium text-gray-700">Rate Purchase</label>
                            <input type="text" class="mt-1 block w-full border border-gray-300 rounded-md p-2" :class="{'border border-red-500':isEmpty.rate}" placeholder="Entrez le taux" v-model="getCurrencyPurchaseId.rate">
                            <span v-if="isEmpty.rate" class="text-danger">{{ msgInput.rate }}</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="">
                            <label class="block text-sm font-medium text-gray-700">Payment Currency</label>
                            <select name="payment_currency_id" id="payment_currency_id" v-model="getCurrencyPurchaseId.payment_currency_id" class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                                <option value="">Select Currency</option>
                                <option v-for="currency in allCurrency" :key="currency.id" :value="currency.id">{{ currency.name }}</option>
                            </select>
                            <span v-if="isEmpty.payment_currency_id" class="text-danger">{{ msgInput.payment_currency_id }}</span>
                        </div>

                        <div class="">
                            <label class="block text-sm font-medium text-gray-700">Total Paid</label>
                            <input type="text" class="mt-1 block w-full border border-gray-300 rounded-md p-2" disabled :class="{'border border-red-500':isEmpty.total_paid}" placeholder="Entrez le total payé" v-model="getCurrencyPurchaseId.total_paid">
                            <span v-if="isEmpty.total_paid" class="text-danger">{{ msgInput.total_paid }}</span>
                        </div>
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
                            Save Change
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
    import {renderGainGraph} from '../plugins/apex'

    const showModal = ref(false)
    const updateModal = ref(false)

    const data = ref({
        currency_id:'',
        supplier:'',
        amount_purchased:'',
        rate:'',
        payment_currency_id:'',
        total_paid:'',
    })
    const isEmpty = ref({})
    const msgInput = ref({})
    const isLoader = ref(false)
    const getCurrencyPurchaseId = ref({})

    const purchases = ref([])
    const allCurrency = ref([]);
    const gain = ref([])

    const AllCurencyPurchases = async ()=>{
        await getData('/currencypurchases').then(res=>{
            purchases.value = res.data.data
        })
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

    const columns = [
        {
            title: 'Supplier Name',
            data: null,
            render: (data, type, row) => {
                return `<div style="display:flex; align-items:center; justify-content: flex-start;font-weight: 600;">
                        <span class="fw-bold" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; line-height: 1.4em; max-height: 2.8em; word-break: break-word;" >${row.supplier}</span>
                    </div>`;
            }
        },
        {
            title: 'Amount Purchased',
            data: 'amount_purchased',
            render: (data, type, row) => {
                return `${Number(row.amount_purchased).toLocaleString("fr-FR")} ${row.currency?.code}`
            }
        },
        {
            title:'Rate',
            data: 'rate',
            render: (data, type, row) => {
                return `${Number(row.rate).toLocaleString("fr-FR")} ${row.payment_currency?.code}`
            }
        },
        {
            title:'Total Paid',
            data: 'total_paid',
            render: (data, type, row) => {
                return `${Number(row.total_paid).toLocaleString("fr-FR")} ${row.payment_currency?.code}`
            }
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
        {
            title: 'Actions',
            data: null,
            orderable: false,
            searchable: false,
            render: function (data, type, row) {
                return `
                    <button class="btn bg-primary text-white me-3" onClick="GetCurrencyPurchaseFunction(${row.id})"><i class="fas fa-edit"></i> Edit</button>
                    <button class="btn bg-danger text-white" onClick="DeleteCurrencyPurchase(${row.id})"><i class="fas fa-trash"></i> Delete</button>
                `;
            }
        }
    ];

    async function AddCurrencyPurchase() {
        for (const field in data.value) {
            isEmpty.value[field] = !data.value[field]
            msgInput.value[field] = `Please enter ${field.replace('_', ' ')}`;
        }
        const allEmpty = Object.values(isEmpty.value).every(value => value === false)
        if(allEmpty){
            isLoader.value = true
            await postData('/addcurrencypurchases', data.value).then(res=>{
                isLoader.value = false
                showModal.value = false
                Swal.fire({
                    position: "center",
                    icon: "success",
                    text: "Add performed",
                    showConfirmButton: false,
                    timer: 1500
                })
                AllCurencyPurchases()
                data.value = {
                    currency_id:'',
                    supplier:'',
                    amount_purchased:'',
                    rate:'',
                    payment_currency_id:'',
                    total_paid:'',
                }
            }).catch(err=>{
                isLoader.value = false
                if(err.response.status === 422){
                    const errors = err.response.data.errors;
                    for (const key in errors) {
                        if (errors.hasOwnProperty(key)) {
                            isEmpty.value[key] = true;
                            msgInput.value[key] = errors[key][0];
                        }
                    }
                }else{
                    Swal.fire(
                        'Error!',
                        'An error occurred while adding the currency purchase.',
                        'error'
                    )
                }
            })
        }
    }

    async function GetCurrencyPurchaseFunction(id) {
        await getSingleData('/currencypurchases/'+id).then(res=>{
            getCurrencyPurchaseId.value = res.data.data
            updateModal.value = true
        })
    }

    async function UpdateCurrencyPurchase(){
        await putData('/updatecurrencypurchases/'+getCurrencyPurchaseId.value.id, getCurrencyPurchaseId.value).then(res =>{
            if(res.status === 200){
                updateModal.value = false
                Swal.fire({
                    position: "center",
                    icon: "success",
                    text: "Update performed",
                    showConfirmButton: false,
                    timer: 1500
                })
                AllCurencyPurchases()
            }
        })
    }

    async function DeleteCurrencyPurchase(id) {
        const result = await Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        })

        if (result.isConfirmed) {
            await deleteData('/deletecurrencypurchases/' + id).then(res => {
                if (res.status === 200) {
                    Swal.fire({
                        position: "center",
                        icon: "success",
                        text: "Delete performed",
                        showConfirmButton: false,
                        timer: 1500
                    })
                    AllCurencyPurchases()
                }
            })
        }
    }

    async function GainFunction() {
        await getData('/gain').then(res=>{
            gain.value = res.data.data

            // Pour chaque "gain", afficher le graph si daily_gains existe
            gain.value.forEach((item, index) => {
                const daily = item.daily_gains

                // Si daily_gains est vide, on saute
                if (!daily || Object.keys(daily).length === 0) return

                const dates = Object.keys(daily)
                const values = Object.values(daily)

                // Appel de ta fonction apex personnalisée
                renderGainGraph(`#gain-chart-${index}`, values, dates)
            })

        })
    }

    onMounted(()=>{
        AllCurencyPurchases()
        AllCurrencyFunction()
        GainFunction()
        window.GetCurrencyPurchaseFunction = GetCurrencyPurchaseFunction
        window.DeleteCurrencyPurchase = DeleteCurrencyPurchase
    })

    watch(
        [() => data.value.amount_purchased, () => data.value.rate],
        ([amount_purchased, rate]) => {
            if (amount_purchased && rate) {
                // Calcul automatique du montant final
                data.value.total_paid = (parseFloat(amount_purchased) * parseFloat(rate)).toFixed(2)
            } else {
                data.value.total_paid = amount_purchased
            }
        }
    )

    watch(
        [() => getCurrencyPurchaseId.value.amount_purchased, () => getCurrencyPurchaseId.value.rate],
        ([amount_purchased, rate]) => {
            if (amount_purchased && rate) {
                // Calcul automatique du montant final
                getCurrencyPurchaseId.value.total_paid = (parseFloat(amount_purchased) * parseFloat(rate)).toFixed(2)
            } else {
                getCurrencyPurchaseId.value.total_paid = amount_purchased
            }
        }
    )

</script>
<style scoped>
    
</style>