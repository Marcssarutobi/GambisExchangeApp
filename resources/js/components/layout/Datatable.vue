<template>
    <div class="table-responsive">
        <DataTable :data="data" :columns="columns" :options="{responsive: true,autoWidth:false,order:[],language:{paginate:{previous:'Previous',next:'Next'}}}" class="table table-center table-hover">
            <thead class=" bg-dark text-white"></thead>

        </DataTable>
    </div>
</template>

<script setup>
    import { DataTable } from 'datatables.net-vue3';
    import DataTableLib from 'datatables.net-bs5'
    import DataTableCore from 'datatables.net'
    import Buttons from 'datatables.net-buttons-bs5'
    import ButtonsHtml5 from 'datatables.net-buttons/js/buttons.html5'
    import print from 'datatables.net-buttons/js/buttons.print'
    import pdfmake from 'pdfmake'
    import pdfFonts from 'pdfmake/build/vfs_fonts'
    import 'datatables.net-responsive-bs5'
    import JSZip from 'jszip';
    import { onMounted, ref, watch } from 'vue';
    window.JSZip = JSZip
    DataTable.use(DataTableLib)
    DataTable.use(DataTableCore)
    DataTable.use(pdfmake)
    DataTable.use(ButtonsHtml5)

    const props = defineProps({
        data:Array,
        columns:{
            type: Array,
            default:()=>[]
        },
        DeleteAllFunction:{
            type: Function,
            required: true
        }
    })

    const Ids = ref([])

    const selectAll = () => {
        document.addEventListener('click', (e) => {
            if (e.target && e.target.id === 'select-all') {
                const isChecked = e.target.checked;
                const checkboxes = document.querySelectorAll('.row-checkbox');

                checkboxes.forEach(cb => {
                    if (!cb.disabled) { // ✅ Ne cocher que si ce n’est pas désactivé
                        cb.checked = isChecked;
                    }
                });

                showSelectedIds();
            }

            if (e.target && e.target.classList.contains('row-checkbox')) {
                const all = document.querySelectorAll('.row-checkbox:not(:disabled)');
                const checked = document.querySelectorAll('.row-checkbox:checked:not(:disabled)');
                const selectAll = document.getElementById('select-all');

                if (selectAll) {
                    selectAll.checked = all.length === checked.length;
                }

                showSelectedIds();
            }
        });
    }

    const showSelectedIds = () => {
        const checkedBoxes = document.querySelectorAll('.row-checkbox:checked');
        Ids.value = Array.from(checkedBoxes).map(cb => cb.getAttribute('data-id'))
    }

    const createButtonAndSearch = () => {
        // Récupérer le conteneur de la recherche
        const searchContainer = document.querySelector('.dt-layout-end');

        // Vérifier si Ids.value contient des éléments sélectionnés
        if (Ids.value.length > 0) {

            // Vérifier si le bouton existe déjà
            let existingBtn = document.querySelector('.custom-delete-btn');
            if (existingBtn) {
                // Si le bouton existe déjà, ne pas le recréer
                existingBtn.style.display = 'block'; // Assurer qu'il est visible
                // Mettre à jour le texte du bouton avec le nombre d'éléments à supprimer
                existingBtn.textContent = `Supprimer (${Ids.value.length})`;
                return; // Sortir de la fonction sans rien faire
            }

            if (searchContainer) {
                // 📦 Créer un wrapper "flex" pour aligner bouton + search
                const wrapper = document.createElement('div');
                wrapper.className = 'd-flex align-items-center gap-2'; // Bootstrap 5 flex classes

                // 👉 Récupérer le champ de recherche existant
                const searchBox = searchContainer.firstChild;
                if (searchBox) {
                    searchContainer.removeChild(searchBox);
                }

                // 🧨 Créer ton bouton Supprimer
                const btn = document.createElement('button');
                btn.textContent = `Supprimer (${Ids.value.length})`;
                btn.className = 'btn btn-lg mt-4 btn-outline-danger custom-delete-btn rounded-0 me-4';
                btn.addEventListener('click', () => {
                    console.log('Bouton Supprimer cliqué');
                    props.DeleteAllFunction(Ids.value)
                });

                // 🧩 Ajouter le bouton puis la search box dans le wrapper
                wrapper.appendChild(btn);
                if (searchBox) wrapper.appendChild(searchBox);

                // 🔁 Ajouter le wrapper dans le conteneur
                searchContainer.appendChild(wrapper);
            }

        } else {
            // Si aucun élément n'est sélectionné, cacher le bouton
            const existingBtn = document.querySelector('.custom-delete-btn');
            if (existingBtn) {
                existingBtn.style.display = 'none'; // Cache le bouton
            }
        }
    };

    watch(Ids, (newValue)=>{
        createButtonAndSearch()
    })

    const applyCustomPagingStyle = () => {
        const target = document.querySelector('.dt-paging nav');
        if (target) {
            target.style.setProperty('position', 'static', 'important');
            target.style.setProperty('height', '50px', 'important');
            target.style.setProperty('width', '350px', 'important');
            target.style.setProperty('display', 'flex', 'important');
            target.style.setProperty('align-items', 'center', 'important');
            target.style.setProperty('justify-content', 'center', 'important');
        }
    };

    onMounted(()=>{
        selectAll()
        applyCustomPagingStyle()

        const tableElement = document.querySelector('.dataTable');
        if (tableElement) {
            tableElement.addEventListener('draw', () => {
                selectAll();
                createButtonAndSearch();
                applyCustomPagingStyle(); // Réappliquer le style
            });
        }

    })

</script>

<style>

@import 'datatables.net-bs5/css/dataTables.bootstrap5.min.css';
/* Pour que la pagination et l'info soient sur la même ligne et visibles */
    .row{
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    td{
        height: 50px !important;
    }
    td, th {
        text-align: left !important; /* ou center */
    }
    .dt-paging nav .pagination{
        display: flex !important;
        align-items: center;
        gap: 30px;
    }
    thead th{
        height: 50px !important;
        padding: 0 10px;
    }
    /* Empêche les retours à la ligne dans toutes les cellules du tableau */
.dataTable td,
.dataTable th {
    white-space: nowrap !important;
    overflow: hidden;        /* coupe le texte qui dépasse */
    text-overflow: ellipsis; /* ajoute "..." si le texte est trop long */
    padding: 0 12px !important;
}

/* Fixe une hauteur pour les cellules */
.dataTable td {
    height: 50px !important;
}

/* Pour que les entêtes aient le même style */
.dataTable thead th {
    height: 50px !important;
    padding: 0 12px !important;
}

/* Optionnel : forcer largeur minimale des colonnes pour éviter qu'elles se collent */
.dataTable th,
.dataTable td {
    min-width: 80px; /* adapte selon le contenu */
}

/* Pagination et info sur la même ligne */
.dt-paging nav .pagination{
    display: flex !important;
    align-items: center;
    gap: 30px;
}

/* Container responsive pour scroll horizontal si trop petit */
.table-responsive {
    overflow-x: auto;
}
</style>
