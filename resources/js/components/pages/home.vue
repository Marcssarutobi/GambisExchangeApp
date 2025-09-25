<template>
    <main>

        <!-- Page Title Start -->
        <div class="flex items-center md:justify-between flex-wrap gap-2 mb-5">
            <h4 class="text-default-900 text-lg font-semibold">Dashboard</h4>

            <div class="md:flex hidden items-center gap-3 text-sm font-semibold">
                <a href="#" class="text-sm font-medium text-default-700">Opatix</a>

                <i class="i-tabler-chevron-right text-lg flex-shrink-0 text-default-500 rtl:rotate-180"></i>

                <a href="#" class="text-sm font-medium text-default-700">Menu</a>

                <i class="i-tabler-chevron-right text-lg flex-shrink-0 text-default-500 rtl:rotate-180"></i>

                <a href="#" class="text-sm font-medium text-default-700" aria-current="page">Dashboard</a>
            </div>
        </div>
        <!-- Page Title End -->

        <div class="grid xl:grid-cols-4 md:grid-cols-2 gap-6 mb-6">
            <div
                class="card group overflow-hidden transition-all duration-500 hover:shadow-lg hover:-translate-y-0.5">
                <div class="card-body">
                    <div class="flex items- justify-between">
                        <div>
                            <p class="text-xs tracking-wide font-semibold uppercase text-default-700 mb-3">Total Balance</p>
                            <h4 class="font-semibold text-2xl text-default-700">{{ balance }} XOF</h4>
                        </div>

                        <div
                            class="rounded-full flex justify-center items-center size-14 bg-primary/10 text-primary">
                            <i class="material-symbols-rounded text-2xl transition-all group-hover:fill-1">payments</i>
                        </div>
                    </div>
                </div>
                <div id="total-balance"></div>
            </div>

            <div
                class="card group overflow-hidden transition-all duration-500 hover:shadow-lg hover:-translate-y-0.5">
                <div class="card-body">
                    <div class="flex items- justify-between">
                        <div>
                            <p class="text-xs tracking-wide font-semibold uppercase text-default-700 mb-3">
                                Total Deposit / Day</p>
                            <h4 class="font-semibold text-2xl text-default-700">{{ deposit }} XOF</h4>
                        </div>

                        <div
                            class="rounded-full flex justify-center items-center size-14 bg-secondary/10 text-secondary">
                            <i class="material-symbols-rounded text-2xl transition-all group-hover:fill-1">arrow_upward</i>
                        </div>
                    </div>
                </div>
                <div id="total-deposit"></div>
            </div>

            <div
                class="card group overflow-hidden transition-all duration-500 hover:shadow-lg hover:-translate-y-0.5">
                <div class="card-body">
                    <div class="flex items- justify-between">
                        <div>
                            <p class="text-xs tracking-wide font-semibold uppercase text-default-700 mb-3"> Total Withdrawal / Day</p>
                            <h4 class="font-semibold text-2xl text-default-700">{{ withdrawal }} XOF</h4>
                        </div>

                        <div
                            class="rounded-full flex justify-center items-center size-14 bg-warning/10 text-warning">
                            <i
                                class="material-symbols-rounded text-2xl transition-all group-hover:fill-1">arrow_downward</i>
                        </div>
                    </div>
                </div>
                <div id="total-withdrawal"></div>
            </div>

            <div
                class="card group overflow-hidden transition-all duration-500 hover:shadow-lg hover:-translate-y-0.5">
                <div class="card-body">
                    <div class="flex items- justify-between">
                        <div>
                            <p class="text-xs tracking-wide font-semibold uppercase text-default-700 mb-3">Customer</p>
                            <h4 class="font-semibold text-2xl text-default-700">{{ clients }}</h4>
                        </div>

                        <div class="rounded-full flex justify-center items-center size-14 bg-danger/10 text-danger">
                            <i
                                class="material-symbols-rounded text-2xl transition-all group-hover:fill-1">person</i>
                        </div>
                    </div>
                </div>
                <div id="total-clients"></div>
            </div>
        </div>

        <div class="grid xl:grid-cols-3 md:grid-cols-2 gap-6 mb-6">
            <div class="xl:col-span-2">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Payment movement / days</h4>
                        <div id="morris-line" class="morris-chart"></div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Exchange Rate</h4>
                    <div id="morris-donut" class="morris-chart"></div>
                </div> <!-- end card-body-->
            </div> <!-- end card-->
        </div>

        <div class="grid xl:grid-cols-2 gap-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Recent Customers</h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="table-auto w-full border-collapse">
                        <thead class="bg-default-100 border-b border-default-200">
                            <tr>
                                <th class="px-4 py-2 text-left">Customer</th>
                                <th class="px-4 py-2 text-left">Phone</th>
                                <th class="px-4 py-2 text-left">Email</th>
                                <th class="px-4 py-2 text-left">Create Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-default-200">
                            <tr class="hover:bg-default-50" v-for="client in lastClients" :key="client.id">
                                <td class="px-4 py-2 flex items-center gap-2">
                                    <a href="javascript:void(0);" class="font-semibold text-default-800">{{ client.nom }} {{ client.prenom }}</a>
                                </td>
                                <td class="px-4 py-2">{{ client.phone }}</td>
                                <td class="px-4 py-2">{{ client.email }}</td>
                                <td class="px-4 py-2">{{ formatDate(client.created_at) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div> <!-- end card-->

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Account Transactions</h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="table-auto w-full text-left border-collapse">
                        <thead class="bg-default-100 border-b border-default-200">
                            <tr>
                                <th class="px-4 py-2">Code Account</th>
                                <th class="px-4 py-2">Amount</th>
                                <th class="px-4 py-2">Rate</th>
                                <th class="px-4 py-2">Type</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-default-200">
                            <tr class="hover:bg-default-50" v-for="movement in lastMovements" :key="movement.id">
                                <td class="px-4 py-2">
                                    <h5 class="text-base font-normal" style="font-weight: 600;">{{ movement.account.code }}</h5>
                                    <span class="text-sm text-default-600">{{ formatDate(movement.created_at) }}</span>
                                </td>
                                <td class="px-4 py-2">
                                    <h5 class="text-base font-normal">{{ movement.amount }} {{ movement.currency?.code }}</h5>
                                </td>
                                <td class="px-4 py-2">
                                    <h5 class="text-lg font-normal">{{ movement.rate ? movement.rate : '0' }} {{ movement.account?.currency?.code }}</h5>
                                </td>
                                <td class="px-4 py-2">
                                    <h5 class="text-base font-normal">{{ movement.type }}</h5>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
        
    </main>
</template>
<script setup>
    import { onMounted, ref } from 'vue';
    import { getData } from '../plugins/api';
    import {renderDepositGraph,renderWithdrawalGraph,renderBalanceGraph,renderClientGraph,renderLineChart,renderDonutChart} from '../plugins/apex'

    const balance = ref('')
    const dailyBalances = ref([])
    const dailyBalanceDates = ref([])
    const deposit = ref('')
    const dailyDeposits = ref([])
    const dailyDates = ref([])
    const withdrawal = ref('')
    const dailyWithdrawals = ref([])
    const dailyWithdrawalDates = ref([])
    const clients = ref('')
    const dailyClientDates = ref([])
    const dailyClients = ref([])
    const lastClients = ref([])
    const lastMovements = ref([])

    async function TotalBalance() {
        await getData('/total-balance').then(res => {
            balance.value = Number(res.data.totalBalance).toLocaleString("fr-FR")
            dailyBalances.value = res.data.dailyDeposits.map(item => parseFloat(item.total))
            dailyBalanceDates.value = res.data.dailyDeposits.map(item => item.date)
            renderBalanceGraph(dailyBalances.value, dailyBalanceDates.value);
        })
    }

    async function TotalDeposit() {
        await getData('/deposits-summary').then(res => {
            deposit.value = Number(res.data.todayDeposits).toLocaleString("fr-FR")
            dailyDeposits.value = res.data.dailyDeposits.map(item => parseFloat(item.total))
            dailyDates.value = res.data.dailyDeposits.map(item => item.date)

            // Affichage du graphique
            renderDepositGraph(dailyDeposits.value, dailyDates.value);
        })
    }

    async function TotalWithdrawal() {
        await getData('/withdrawals-summary').then(res => {
            withdrawal.value = Number(res.data.todayWithdrawals).toLocaleString("fr-FR")
            dailyWithdrawals.value = res.data.dailyWithdrawals.map(item => parseFloat(item.total))
            dailyWithdrawalDates.value = res.data.dailyWithdrawals.map(item => item.date)

            renderWithdrawalGraph(dailyWithdrawals.value, dailyWithdrawalDates.value);
        })
    }

    async function TotalClients() {
        await getData('/total-clients').then(res => {
            clients.value = res.data.totalClients
            dailyClients.value = res.data.dailyClients.map(item => parseFloat(item.total))
            dailyClientDates.value = res.data.dailyClients.map(item => item.date)
            renderClientGraph(dailyClients.value, dailyClientDates.value);
        })
    }

    async function FinancialSummary() {
        await getData('/financial-summary').then(res => {
            renderLineChart("morris-line",res.data);
        });
    }

    async function LastClientFunction() {
        await getData('/last-clients').then(res => {
            lastClients.value = res.data
        });
    }

    async function ExchangeRatesDonut() {
        await getData('/exchange-rates-donut').then(res => {
            renderDonutChart("morris-donut",res.data);
        });
    }

    function formatDate(dateString, format = "fr") {
        if (!dateString) return "";

        const date = new Date(dateString);

        switch (format) {
            case "fr": // format français JJ/MM/AAAA
                return date.toLocaleDateString("fr-FR", {
                    day: "2-digit",
                    month: "2-digit",
                    year: "numeric"
                });
            case "us": // format US MM/DD/YYYY
                return date.toLocaleDateString("en-US", {
                    day: "2-digit",
                    month: "2-digit",
                    year: "numeric"
                });
            case "long": // format long (ex: 25 septembre 2025)
                return date.toLocaleDateString("fr-FR", {
                    day: "2-digit",
                    month: "long",
                    year: "numeric"
                });
            default:
                return date.toISOString().split("T")[0]; // format ISO YYYY-MM-DD
        }
    }

    async function LastMovements() {
        await getData('/last-movements').then(res => {
            lastMovements.value = res.data
        });
    }

    onMounted(() => {
        TotalBalance()
        TotalDeposit()
        TotalWithdrawal()
        TotalClients()
        FinancialSummary()
        ExchangeRatesDonut()
        LastClientFunction()
        LastMovements()
    })

</script>
<style scoped>
    
</style>