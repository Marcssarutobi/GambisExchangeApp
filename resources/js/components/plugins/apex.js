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