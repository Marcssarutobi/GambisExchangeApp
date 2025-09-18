<template>
    <main>

        <!-- Page Title Start -->
        <div class="flex items-center md:justify-between flex-wrap gap-2 mb-5">
            <h4 class="text-default-900 text-lg font-semibold">Currency list</h4>

            <div class="md:flex hidden items-center gap-3 text-sm font-semibold">
                <RouterLink to="/" class="text-sm font-medium text-default-700">Home</RouterLink>

                <i class="i-tabler-chevron-right text-lg flex-shrink-0 text-default-500 rtl:rotate-180"></i>

                <RouterLink to="/customer" class="text-sm font-medium text-default-700" aria-current="page">Currency list</RouterLink>
            </div>
        </div>
        <!-- Page Title End -->

        <div class="col-lg-12 mt-8">
            <div class="card overflow-hidden p-3">
                <div class="card-header text-end">
                    <button type="button" @click="showModal = true" class="btn btn-lg bg-primary text-white">Add Currency</button>
                </div>
                <div class="overflow-x-auto">
                    <div class="min-w-full inline-block align-middle">
                        <div class="overflow-hidden">
                            <DataTable :data="allCurrency" :columns="columns" />
                        </div>
                    </div>
                </div>
                
            </div> <!-- end card -->
        </div>

        <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center" style="z-index: 1000;">
            <div class="bg-white rounded-lg p-6 w-1/3">
                <h2 class="text-lg font-semibold">Add a currency</h2>


                <form class="mt-3 space-y-4" @submit.prevent="AddCurrencyFunction">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="">
                            <label class="block text-sm font-medium text-gray-700">Code</label>
                            <input type="text" class="mt-1 block w-full border border-gray-300 rounded-md p-2" :class="{'border border-red-500':isEmpty.code}" placeholder="Ex: USD" v-model="data.code">
                            <span v-if="isEmpty.code" class="text-danger">{{ msgInput.code }}</span>
                        </div>
    
                        <div class="">
                            <label class="block text-sm font-medium text-gray-700">Nom</label>
                            <input type="text" class="mt-1 block w-full border border-gray-300 rounded-md p-2" :class="{'border border-red-500':isEmpty.name}" placeholder="Name Currency" v-model="data.name">
                            <span v-if="isEmpty.name" class="text-danger">{{ msgInput.name }}</span>
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
            <div class="bg-white rounded-lg p-6 w-1/3">
                <h2 class="text-lg font-semibold">Update a currency</h2>


                <form class="mt-3 space-y-4" @submit.prevent="UpdateCurrencyFunction">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="">
                            <label class="block text-sm font-medium text-gray-700">Code</label>
                            <input type="text" class="mt-1 block w-full border border-gray-300 rounded-md p-2" :class="{'border border-red-500':isEmpty.code}" placeholder="Ex: USD" v-model="getCurrency.code">
                            <span v-if="isEmpty.code" class="text-danger">{{ msgInput.code }}</span>
                        </div>
    
                        <div class="">
                            <label class="block text-sm font-medium text-gray-700">Nom</label>
                            <input type="text" class="mt-1 block w-full border border-gray-300 rounded-md p-2" :class="{'border border-red-500':isEmpty.name}" placeholder="Name Currency" v-model="getCurrency.name">
                            <span v-if="isEmpty.name" class="text-danger">{{ msgInput.name }}</span>
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

    const allCurrency = ref([]);
    const data = ref({
        code: '',
        name: '',
    });
    const isEmpty = ref({})
    const msgInput = ref({})
    const isLoader = ref(false)
    const getCurrency = ref({})
    
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
            title: 'Name',
            data: 'name', // Pas de data directe car on combine deux champs
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
                    <button class="btn bg-primary text-white me-3" onClick="ShowCurrencyFunction(${row.id})"><i class="fas fa-edit"></i> Edit</button>
                    <button class="btn bg-danger text-white" onClick="DeleteCurrencyFunction(${row.id})"><i class="fas fa-trash"></i> Delete</button>
                `;
            }
        }
    ];

    async function AddCurrencyFunction(){
        for (const field in data.value) {
            isEmpty.value[field] = !data.value[field]
            msgInput.value[field] = `Please enter ${field.replace('_', ' ')}`;
        }
        const allEmpty = Object.values(isEmpty.value).every(value => value === false)
        if(allEmpty){
            try {
                isLoader.value = true
                await postData('/addcurrencies',data.value).then(res=>{
                    isLoader.value = false
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Currency added successfully',
                        timer: 2000,
                        showConfirmButton: false,
                    });
                    data.value = {
                        code: '',
                        name: '',
                    }
                    showModal.value = false
                    AllCurrencyFunction()
                })
            } catch (error) {
                console.error("Error adding currency:", error);
            }
        }
        
    }

    async function ShowCurrencyFunction(id) {
        await getSingleData(`/currencies/${id}`).then(res=>{
            getCurrency.value = res.data.data
            updateModal.value = true
        })
    }

    async function UpdateCurrencyFunction(){
        isLoader.value = true
        await putData('/updatecurrencies/'+getCurrency.value.id,getCurrency.value).then(res=>{
            if (res.status === 200) {
                isLoader.value = false
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Currency updated successfully',
                    timer: 2000,
                    showConfirmButton: false,
                });
                getCurrency.value = {}
                updateModal.value = false
                AllCurrencyFunction()
                
            }
        })
    }

    async function DeleteCurrencyFunction(id){
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
                await deleteData('/deletecurrencies/'+id).then(res=>{
                    if (res.status === 200) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: 'Currency has been deleted.',
                            timer: 2000,
                            showConfirmButton: false,
                        });
                        AllCurrencyFunction()
                    }
                })
            }
        })
    }

    onMounted(() => {
        AllCurrencyFunction();
        window.ShowCurrencyFunction = ShowCurrencyFunction
        window.DeleteCurrencyFunction = DeleteCurrencyFunction
    });

</script>
<style scoped>
    
</style>