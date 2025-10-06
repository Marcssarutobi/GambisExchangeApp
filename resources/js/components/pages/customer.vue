<template>
    <main>

        <!-- Page Title Start -->
        <div class="flex items-center md:justify-between flex-wrap gap-2 mb-5">
            <h4 class="text-default-900 text-lg font-semibold">Customer list</h4>

            <div class="md:flex hidden items-center gap-3 text-sm font-semibold">
                <RouterLink to="/" class="text-sm font-medium text-default-700">Home</RouterLink>

                <i class="i-tabler-chevron-right text-lg flex-shrink-0 text-default-500 rtl:rotate-180"></i>

                <RouterLink to="/customer" class="text-sm font-medium text-default-700" aria-current="page">Customer list</RouterLink>
            </div>
        </div>
        <!-- Page Title End -->

        <div class="col-lg-12 mt-8">
            <div class="card overflow-hidden p-3">
                <div class="card-header text-end">
                    <button type="button"  @click="showModal = true" class="btn btn-lg bg-primary text-white">Add Customer</button>
                </div>
                <div class="overflow-x-auto">
                    <div class="min-w-full inline-block align-middle">
                        <div class="overflow-hidden">
                            <DataTable :data="allClients" :columns="columns" />
                        </div>
                    </div>
                </div>
                
            </div> <!-- end card -->
        </div>

        <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center" style="z-index: 1000;">
            <div class="bg-white rounded-lg p-6 w-full sm:w-3/4 md:w-2/3 lg:w-1/3 max-h-[90vh] lg:max-w-[50%] overflow-y-auto">
                <h2 class="text-lg font-semibold">Add a customer</h2>


                <form class="mt-3 space-y-4" @submit.prevent="AddCustomer">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="">
                            <label class="block text-sm font-medium text-gray-700">Nom</label>
                            <input type="text" class="mt-1 block w-full border border-gray-300 rounded-md p-2" :class="{'border border-red-500':isEmpty.nom}" placeholder="Entrez votre nom" v-model="data.nom">
                            <span v-if="isEmpty.nom" class="text-danger">{{ msgInput.nom }}</span>
                        </div>
    
                        <div class="">
                            <label class="block text-sm font-medium text-gray-700">Prénom</label>
                            <input type="text" class="mt-1 block w-full border border-gray-300 rounded-md p-2" :class="{'border border-red-500':isEmpty.prenom}" placeholder="Entrez votre prénom" v-model="data.prenom">
                            <span v-if="isEmpty.prenom" class="text-danger">{{ msgInput.prenom }}</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="">
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" class="mt-1 block w-full border border-gray-300 rounded-md p-2" :class="{'border border-red-500':isEmpty.email}" placeholder="Entrez votre email" v-model="data.email">
                            <span v-if="isEmpty.email" class="text-danger">{{ msgInput.email }}</span>
                        </div>
    
                        <div class="">
                            <label class="block text-sm font-medium text-gray-700">Téléphone</label>
                            <input type="text" class="mt-1 block w-full border border-gray-300 rounded-md p-2" :class="{'border border-red-500':isEmpty.phone}" placeholder="Entrez votre téléphone" v-model="data.phone">
                            <span v-if="isEmpty.phone" class="text-danger">{{ msgInput.phone }}</span>
                        </div>
                    </div>
                    <div class="">
                        <label class="block text-sm font-medium text-gray-700">Numéro pièce d'identité</label>
                        <input type="text" class="mt-1 block w-full border border-gray-300 rounded-md p-2" :class="{'border border-red-500':isEmpty.npiece}" placeholder="Entrez votre numéro pièce" v-model="data.npiece">
                        <span v-if="isEmpty.npiece" class="text-danger">{{ msgInput.npiece }}</span>
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
                <h2 class="text-lg font-semibold">Update a customer</h2>


                <form class="mt-3 space-y-4" @submit.prevent="UpdateClient">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="">
                            <label class="block text-sm font-medium text-gray-700">Nom</label>
                            <input type="text" class="mt-1 block w-full border border-gray-300 rounded-md p-2" :class="{'border border-red-500':isEmpty.nom}" placeholder="Entrez votre nom" v-model="getClient.nom">
                            <span v-if="isEmpty.nom" class="text-danger">{{ msgInput.nom }}</span>
                        </div>
    
                        <div class="">
                            <label class="block text-sm font-medium text-gray-700">Prénom</label>
                            <input type="text" class="mt-1 block w-full border border-gray-300 rounded-md p-2" :class="{'border border-red-500':isEmpty.prenom}" placeholder="Entrez votre prénom" v-model="getClient.prenom">
                            <span v-if="isEmpty.prenom" class="text-danger">{{ msgInput.prenom }}</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="">
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" class="mt-1 block w-full border border-gray-300 rounded-md p-2" :class="{'border border-red-500':isEmpty.email}" placeholder="Entrez votre email" v-model="getClient.email">
                            <span v-if="isEmpty.email" class="text-danger">{{ msgInput.email }}</span>
                        </div>
    
                        <div class="">
                            <label class="block text-sm font-medium text-gray-700">Téléphone</label>
                            <input type="text" class="mt-1 block w-full border border-gray-300 rounded-md p-2" :class="{'border border-red-500':isEmpty.phone}" placeholder="Entrez votre téléphone" v-model="getClient.phone">
                            <span v-if="isEmpty.phone" class="text-danger">{{ msgInput.phone }}</span>
                        </div>
                    </div>
                    <div class="">
                        <label class="block text-sm font-medium text-gray-700">Numéro pièce d'identité</label>
                        <input type="text" class="mt-1 block w-full border border-gray-300 rounded-md p-2" :class="{'border border-red-500':isEmpty.npiece}" placeholder="Entrez votre numéro pièce" v-model="getClient.npiece">
                        <span v-if="isEmpty.npiece" class="text-danger">{{ msgInput.npiece }}</span>
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
                            Save changes
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

    const allClients = ref([]);
    const data = ref({
        nom: '',
        prenom: '',
        phone: '',
        email: '',
        npiece: ''
    });
    const isEmpty = ref({})
    const msgInput = ref({})
    const isLoader = ref(false)
    const getClient = ref({})
    
    const showModal = ref(false)
    const updateModal = ref(false)

    async function AllCustomer() {
        try {
            await getData('/clients').then(res=>{
                allClients.value = res.data.data
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
            title: 'Full Name',
            data: null,
            render: (data, type, row) => {
                return `<div style="display:flex; align-items:center; justify-content: flex-start;font-weight: 600;">
                        <span class="fw-bold" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; line-height: 1.4em; max-height: 2.8em; word-break: break-word;" >${row.nom} ${row.prenom}</span>
                    </div>`;
            }
        },
        {
            title: 'Email',
            data: 'email', // Pas de data directe car on combine deux champs
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
                    <button class="btn bg-primary text-white me-3" onClick="ShowClient(${row.id})"><i class="fas fa-edit"></i> Edit</button>
                    <button class="btn bg-danger text-white" onClick="DeleteClient(${row.id})"><i class="fas fa-trash"></i> Delete</button>
                `;
            }
        }
    ];

    async function AddCustomer() {
        for (const field in data.value) {
            isEmpty.value[field] = !data.value[field]
            msgInput.value[field] = `Please enter ${field.replace('_', ' ')}`;
        }
        const allEmpty = Object.values(isEmpty.value).every(value => value === false)
        if(allEmpty){
            isLoader.value = true
            await postData('/addclients',data.value).then(res=>{
                if (res.status === 201) {
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
                        nom: '',
                        prenom: '',
                        phone: '',
                        email: '',
                        npiece: ''
                    }
                    AllCustomer();
                }
            })
        }
    }

    async function ShowClient(id) {
        await getSingleData('/clients/'+ id).then(res=>{
            getClient.value = res.data.data
            updateModal.value = true
        })
    }

    async function UpdateClient(){
        isLoader.value = true
        await putData('/updateclients/'+getClient.value.id, getClient.value).then(res=>{
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
                getClient.value = {}
                AllCustomer();
            }
        })
    }

    async function DeleteClient(id) {
        Swal.fire({
            title: "Are you sure you want to delete this customer?",
            text: "This action is irreversible.",
            icon: "warning",
            showCancelButton: true,
            cancelButtonColor: "#d33",
            confirmButtonColor: "#3085d6",
            confirmButtonText: "Delete",
            cancelButtonText: "Close"
        }).then(async (result) => {
            if (result.isConfirmed) {

                // Affiche un loader pendant la suppression
                Swal.fire({
                    text: "Deletion in progress...",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                await deleteData('/deleteclients/'+id).then(res=>{
                    if (res.status === 200) {
                        Swal.fire({
                            position: "center",
                            icon: "success",
                            text: "Delete performed",
                            showConfirmButton: false,
                            timer: 1500
                        })
                        AllCustomer();
                    }
                }).catch(error => {
                    console.error("Error deleting customer:", error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while deleting the customer.'
                    });
                });
            }
        })
    }

    onMounted(() => {
        AllCustomer();
        window.ShowClient = ShowClient
        window.DeleteClient = DeleteClient
    });

</script>
<style >
    
</style>