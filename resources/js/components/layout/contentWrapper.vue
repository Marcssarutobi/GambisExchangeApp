<template>
    <div class="wrapper">
        <sidebar></sidebar>
        <div class="page-content">
            <headers></headers>
            <router-view></router-view>
        </div>
    </div>
</template>
<script setup>

    import sidebar from './sidebar.vue';
    import headers from './header.vue';

    import { loadChartLibrary } from '../plugins/chart'
    import { loadDashboardLibrary } from '../plugins/dashboard'
    import { loadInitAppLibrary } from '../plugins/initapp'

    import { onMounted, watch, ref } from "vue";
    import { useRoute } from "vue-router";

    const route = useRoute();

    function initializePlugins(){
        setTimeout(() => {
            loadChartLibrary();
            loadDashboardLibrary();
            loadInitAppLibrary();
        }, 0);
    }

    onMounted(() => {
        initializePlugins();
    });

    watch(route, () => {
        initializePlugins();
    });

</script>
<style scoped>
    
</style>