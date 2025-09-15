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
                    <button type="button" class="btn btn-lg bg-primary text-white">Add Exchanges</button>
                </div>
                <div class="overflow-x-auto">
                    <div class="min-w-full inline-block align-middle">
                        <div class="overflow-hidden">
                            <DataTable  />
                        </div>
                    </div>
                </div>
                
            </div> <!-- end card -->
        </div>

    </main>
</template>
<script setup>

    import DataTable from '../layout/Datatable.vue';

</script>
<style scoped>
    
</style>