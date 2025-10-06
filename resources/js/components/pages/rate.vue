<template>
    <main>

        <!-- Page Title Start -->
        <div class="flex items-center md:justify-between flex-wrap gap-2 mb-5">
            <h4 class="text-default-900 text-lg font-semibold">Exchanges Rate list</h4>

            <div class="md:flex hidden items-center gap-3 text-sm font-semibold">
                <RouterLink to="/" class="text-sm font-medium text-default-700">Home</RouterLink>

                <i class="i-tabler-chevron-right text-lg flex-shrink-0 text-default-500 rtl:rotate-180"></i>

                <RouterLink to="/customer" class="text-sm font-medium text-default-700" aria-current="page">Exchanges Rate list</RouterLink>
            </div>
        </div>
        <!-- Page Title End -->

        <div class="col-lg-12 mt-8">
            <div class="card overflow-hidden p-3">
                <div class="card-header text-end">
                    <button type="button" @click="showModal = true" class="btn btn-lg bg-primary text-white">Add Exchanges rate</button>
                </div>
                <div class="overflow-x-auto">
                    <div class="min-w-full inline-block align-middle">
                        <div class="overflow-hidden">
                            <DataTable :data="allExchange" :columns="columns" />
                        </div>
                    </div>
                </div>
                
            </div> <!-- end card -->
        </div>

        <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center" style="z-index: 1000;">
            <div class="bg-white rounded-lg p-6 w-full sm:w-3/4 md:w-2/3 lg:w-1/3 max-h-[90vh] lg:max-w-[50%] overflow-y-auto">
                <h2 class="text-lg font-semibold">Add a Exchange</h2>


                <form class="mt-3 space-y-4" @submit.prevent="AddExchangeRate">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="">
                            <label class="block text-sm font-medium text-gray-700">From Currency</label>
                            <select name="currency_id" id="currency_id" v-model="data.from_currency_id" class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                                <option value="">Select Currency</option>
                                <option v-for="currency in allCurrency" :key="currency.id" :value="currency.id">{{ currency.name }}</option>
                            </select>
                            <span v-if="isEmpty.from_currency_id" class="text-danger">{{ msgInput.from_currency_id }}</span>
                        </div>
    
                        <div class="">
                            <label class="block text-sm font-medium text-gray-700">To Currency</label>
                            <select name="currency_id" id="currency_id" v-model="data.to_currency_id" class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                                <option value="">Select Currency</option>
                                <option v-for="currency in allCurrency" :key="currency.id" :value="currency.id">{{ currency.name }}</option>
                            </select>
                            <span v-if="isEmpty.to_currency_id" class="text-danger">{{ msgInput.to_currency_id }}</span>
                        </div>
                    </div>
                    <div class="">
                        <label class="block text-sm font-medium text-gray-700">Rate</label>
                        <input type="text" class="mt-1 block w-full border border-gray-300 rounded-md p-2" :class="{'border border-red-500':isEmpty.rate}" placeholder="Enter the rate" v-model="data.rate">
                        <span v-if="isEmpty.rate" class="text-danger">{{ msgInput.rate }}</span>
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
            <div class="bg-white rounded-lg p-6 w-full sm:w-3/4 md:w-2/3 lg:w-1/3 max-h-[90vh] lg:max-w-[50%] overflow-y-auto">
                <h2 class="text-lg font-semibold">Update a Exchange</h2>


                <form class="mt-3 space-y-4" @submit.prevent="UpdateExchangeFunction">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="">
                            <label class="block text-sm font-medium text-gray-700">From Currency</label>
                            <select name="currency_id" id="currency_id" v-model="getEchange.from_currency_id" class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                                <option value="">Select Currency</option>
                                <option v-for="currency in allCurrency" :key="currency.id" :value="currency.id">{{ currency.name }}</option>
                            </select>
                            <span v-if="isEmpty.from_currency_id" class="text-danger">{{ msgInput.from_currency_id }}</span>
                        </div>
    
                        <div class="">
                            <label class="block text-sm font-medium text-gray-700">To Currency</label>
                            <select name="currency_id" id="currency_id" v-model="getEchange.to_currency_id" class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                                <option value="">Select Currency</option>
                                <option v-for="currency in allCurrency" :key="currency.id" :value="currency.id">{{ currency.name }}</option>
                            </select>
                            <span v-if="isEmpty.to_currency_id" class="text-danger">{{ msgInput.to_currency_id }}</span>
                        </div>
                    </div>
                    <div class="">
                        <label class="block text-sm font-medium text-gray-700">Rate</label>
                        <input type="text" class="mt-1 block w-full border border-gray-300 rounded-md p-2" :class="{'border border-red-500':isEmpty.rate}" placeholder="Enter the rate" v-model="getEchange.rate">
                        <span v-if="isEmpty.rate" class="text-danger">{{ msgInput.rate }}</span>
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

    </main>
</template>
<script setup>

    import { onMounted, ref } from 'vue';
    import DataTable from '../layout/Datatable.vue';
    import { deleteData, getData, getSingleData, postData, putData } from '../plugins/api';
    import Swal from 'sweetalert2';

    const allExchange = ref([]);
    const allCurrency = ref([]);
    const data = ref({
        from_currency_id: '',
        to_currency_id: '',
        rate: '',
    });
    const isEmpty = ref({})
    const msgInput = ref({})
    const isLoader = ref(false)
    const getEchange = ref({})
    
    const showModal = ref(false)
    const updateModal = ref(false)

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
            title: 'From Currency',
            data: 'from_currency.code', // Pas de data directe car on combine deux champs
        },
        {
            title: 'To Currency',
            data: 'to_currency.code', // Pas de data directe car on combine deux champs
        },
        {
            title: 'Rate',
            data: 'rate',
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
                    <button class="btn bg-primary text-white me-3" onClick="ShowExchangeFunction(${row.id})"><i class="fas fa-edit"></i> Edit</button>
                    <button class="btn bg-danger text-white" onClick="DeleteExchangeFunction(${row.id})"><i class="fas fa-trash"></i> Delete</button>
                `;
            }
        }
    ];

    async function AddExchangeRate() {
        for (const field in data.value) {
            isEmpty.value[field] = !data.value[field]
            msgInput.value[field] = `Please enter ${field.replace('_', ' ')}`;
        }

        // Vérification que les deux devises ne sont pas identiques
        if (data.value.from_currency_id && data.value.to_currency_id &&
            data.value.from_currency_id === data.value.to_currency_id) {
            
            Swal.fire({
                icon: "error",
                title: "Invalid selection",
                text: "From currency and To currency cannot be the same!",
            });
            return; // on stoppe la fonction ici
        }

        const allEmpty = Object.values(isEmpty.value).every(value => value === false)
        if(allEmpty){
            isLoader.value = true
            await postData('/addexchangerates',data.value).then(res=>{
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
                        from_currency_id: '',
                        to_currency_id: '',
                        rate: '',
                    }
                    AllExchangeRate();
                }
            })
        }
    }

    async function ShowExchangeFunction(id) {
        await getSingleData(`/exchangerates/${id}`).then(res=>{
            getEchange.value = res.data.data
            updateModal.value = true
        })
    }

    async function UpdateExchangeFunction() {
        isLoader.value = true
        await putData(`/updateexchangerates/${getEchange.value.id}`, getEchange.value).then(res=>{
            if (res.status === 200) {
                isLoader.value = false
                updateModal.value = false
                Swal.fire({
                    position: "center",
                    icon: "success",
                    text: "Update performed",
                    showConfirmButton: false,
                    timer: 1500
                })
                getEchange.value = {}
                AllExchangeRate();
            }
        })
    }

    async function DeleteExchangeFunction(id) {
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
                    await deleteData(`/deleteexchangerates/${id}`).then(res => {
                        if (res.status === 200) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'Exchange rate has been deleted.',
                                timer: 2000,
                                showConfirmButton: false
                            });
                            AllExchangeRate();
                        }
                    })
                } catch (error) {
                    console.error("Error deleting account:", error);
                }
            }
        })
    }

    onMounted(()=>{
        AllExchangeRate()
        AllCurrencyFunction()
        window.ShowExchangeFunction = ShowExchangeFunction
        window.DeleteExchangeFunction = DeleteExchangeFunction
    })

</script>
<style scoped>
    
</style>