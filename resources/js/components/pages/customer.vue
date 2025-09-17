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
            <div class="bg-white rounded-lg p-6 w-1/3">
                <h2 class="text-lg font-semibold">Add a customer</h2>


                <form class="mt-3 space-y-4">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="">
                            <label class="block text-sm font-medium text-gray-700">Nom</label>
                            <input type="text" class="mt-1 block w-full border border-gray-300 rounded-md p-2" placeholder="Entrez votre nom">
                        </div>
    
                        <div class="">
                            <label class="block text-sm font-medium text-gray-700">Prénom</label>
                            <input type="text" class="mt-1 block w-full border border-gray-300 rounded-md p-2" placeholder="Entrez votre prénom">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="">
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" class="mt-1 block w-full border border-gray-300 rounded-md p-2" placeholder="Entrez votre email">
                        </div>
    
                        <div class="">
                            <label class="block text-sm font-medium text-gray-700">Téléphone</label>
                            <input type="text" class="mt-1 block w-full border border-gray-300 rounded-md p-2" placeholder="Entrez votre téléphone">
                        </div>
                    </div>
                    <div class="">
                        <label class="block text-sm font-medium text-gray-700">Numéro pièce d'identité</label>
                        <input type="text" class="mt-1 block w-full border border-gray-300 rounded-md p-2" placeholder="Entrez votre numéro pièce">
                    </div>

                    <div class="mt-4 flex justify-end gap-2">
                        <button class="px-4 py-2 bg-gray-200 rounded" @click="showModal = false">
                            Fermer
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">
                            Sauvegarder
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
    import { getData } from '../plugins/api';

    let addmodal;
    let updatemodal;

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
                return `<div style="display:flex; align-items:center; justify-content: flex-start;">
                        <span class="fw-bold" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; line-height: 1.4em; max-height: 2.8em; word-break: break-word;" >${row.nom} ${row.prenom}</span>
                    </div>`;
            }
        },
        {
            title: 'Phone',
            data: 'phone', // Pas de data directe car on combine deux champs
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
                    <div class="d-flex justify-content-center">
                        <div class="dropdown dropdown-action">
                            <a href="#" class="btn-action-icon" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-ellipsis-v"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" onClick="showEventFunction(${row.id})"><i class="fas fa-edit"></i> Edit</a>
                                <button class="dropdown-item delete-project" onClick="DeleteEventFunction(${row.id})"><i class="fas fa-trash"></i> Delete</button>
                            </div>
                        </div>
                    </div>
                `;
            }
        }
    ];

    onMounted(() => {
        AllCustomer();
    });

</script>
<style scoped>
    
</style>