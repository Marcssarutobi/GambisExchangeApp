export function renderDepositGraph(data = [], categories = []){
    var options = {
        series: [
            { name: "Total Deposit", data: data },
        ],
        chart: {
            height: 65,
            type: "area",
            sparkline: { enabled: !0 },
            zoom: { enabled: !1 },
        },
        dataLabels: { enabled: !1 },
        stroke: { width: 2, curve: "smooth" },
        fill: {
            type: "gradient",
            gradient: {
                shade: "dark",
                gradientToColors: ["#6366f1"],
                shadeIntensity: 1,
                type: "vertical",
                opacityFrom: 0.75,
                opacityTo: 0.1,
            },
        },
        colors: ["#6366f1"],
        tooltip: {
            fixed: { enabled: !1 },
            x: { show: !1 },
            y: {
                title: {
                    formatter: function () {
                        return "";
                    },
                },
            },
            marker: { show: !1 },
        },
        xaxis: {
            categories: categories,
        },
    },
    chart = new ApexCharts(document.querySelector("#total-deposit"), options)
    chart.render();
}

export function renderWithdrawalGraph(data = [], categories = []){
    var options = {
        series: [
            { name: "Total Withdrawal", data: data },
        ],
        chart: {
            height: 65,
            type: "area",
            sparkline: { enabled: !0 },
            zoom: { enabled: !1 },
        },
        dataLabels: { enabled: !1 },
        stroke: { width: 2, curve: "smooth" },
        fill: {
            type: "gradient",
            gradient: {
                shade: "dark",
                gradientToColors: ["#f43f5e"],
                shadeIntensity: 1,
                type: "vertical",
                opacityFrom: 0.75,
                opacityTo: 0.1,
            },
        },
        colors: ["#f43f5e"],
        tooltip: {
            fixed: { enabled: !1 },
            x: { show: !1 },
            y: {
                title: {
                    formatter: function () {
                        return "";
                    },
                },
            },
            marker: { show: !1 },
        },
        xaxis: {
            categories: categories,
        },
    },
    chart = new ApexCharts(document.querySelector("#total-withdrawal"), options)
    chart.render(); 
}

export function renderBalanceGraph(data = [], categories = []){
    var options = {
        series: [
            { name: "Total Balance", data: data },
        ],
        chart: {
            height: 65,
            type: "area",
            sparkline: { enabled: !0 },
            zoom: { enabled: !1 },
        },
        dataLabels: { enabled: !1 },
        stroke: { width: 2, curve: "smooth" },
        fill: {
            type: "gradient",
            gradient: {
                shade: "dark",
                gradientToColors: ["#4ade80"],
                shadeIntensity: 1,
                type: "vertical",
                opacityFrom: 0.75,
                opacityTo: 0.1,
            },
        },
        colors: ["#4ade80"],
        tooltip: {
            fixed: { enabled: !1 },
            x: { show: !1 },
            y: {
                title: {
                    formatter: function () {
                        return "";
                    },
                },
            },
            marker: { show: !1 },
        },
        xaxis: {
            categories: categories,
        },
    },
    chart = new ApexCharts(document.querySelector("#total-balance"), options)
    chart.render();
}

export function renderClientGraph(data = [], categories = []){
    var options = {
        series: [
            { name: "Total Clients", data: data },
        ],
        chart: {
            height: 65,
            type: "area",
            sparkline: { enabled: !0 },
            zoom: { enabled: !1 },
        },
        dataLabels: { enabled: !1 },
        stroke: { width: 2, curve: "smooth" },
        fill: {
            type: "gradient",
            gradient: {
                shade: "dark",
                gradientToColors: ["#e9ecef"],
                shadeIntensity: 1,
                type: "vertical",
                opacityFrom: 0.75,
                opacityTo: 0.1,
            },
        },
        colors: ["#e9ecef"],
        tooltip: {
            fixed: { enabled: !1 },
            x: { show: !1 },
            y: {
                title: {
                    formatter: function () {
                        return "";
                    },
                },
            },
            marker: { show: !1 },
        },
        xaxis: {
            categories: categories,
        },
    },
    chart = new ApexCharts(document.querySelector("#total-clients"), options)
    chart.render();
}

export function renderLineChart(elementId, data) {
    if (!document.getElementById(elementId)) return;

    Morris.Line({
        element: elementId,
        gridLineColor: "#eef0f2",
        lineColors: ["#28a745", "#dc3545", "#007bff"], // Dépôts, Retraits, Balance
        data: data,
        xkey: "y",
        ykeys: ["deposits", "withdrawals", "balance"],
        labels: ["Dépôts", "Retraits", "Balance"],
        hideHover: "auto",
        resize: true,
    });
}

export function renderDonutChart(elementId, data) {
    if (!document.getElementById(elementId)) return;

    Morris.Donut({
        element: elementId,
        resize: true,
        backgroundColor: "transparent",
        colors: [
            "#574bd6",
            "#6d61ea",
            "#877de8",
            "#9b94da",
            "#c5bff5",
        ],
        data: data, // on reçoit les données en paramètre
    });
}