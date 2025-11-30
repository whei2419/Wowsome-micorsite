document.addEventListener("DOMContentLoaded", function () {
    // Get chart data from data attributes
    const overviewElement = document.querySelector("#chart-customers-overview");
    const registrationDates = JSON.parse(overviewElement.dataset.dates);
    const registrationCounts = JSON.parse(overviewElement.dataset.counts);

    // Customers Overview Chart with Real Data
    var optionsOverview = {
        series: [{
            name: "Registration",
            data: registrationCounts
        }],
        chart: {
            height: 300,
            type: 'line',
            zoom: {
                enabled: false
            },
            toolbar: {
                show: false
            }
        },
        dataLabels: {
            enabled: true,
            style: {
                fontSize: '12px',
                fontWeight: 'bold',
                colors: ['#206bc4']
            },
            background: {
                enabled: true,
                foreColor: '#fff',
                borderRadius: 2,
                padding: 4,
                opacity: 0.9,
                borderWidth: 1,
                borderColor: '#206bc4'
            }
        },
        stroke: {
            curve: 'smooth',
            width: 3
        },
        xaxis: {
            categories: registrationDates,
            labels: {
                rotate: -45,
                rotateAlways: true,
                style: {
                    fontSize: '11px'
                }
            }
        },
        yaxis: {
            title: {
                text: 'Registrations'
            },
            labels: {
                formatter: function(val) {
                    return Math.floor(val);
                }
            }
        },
        colors: ['#206bc4'],
        grid: {
            borderColor: '#e7e7e7',
            row: {
                colors: ['#f3f3f3', 'transparent'],
                opacity: 0.5
            },
        },
        markers: {
            size: 6,
            colors: ['#206bc4'],
            strokeColors: '#fff',
            strokeWidth: 2,
            hover: {
                size: 8
            }
        },
        tooltip: {
            y: {
                formatter: function(val) {
                    return val + " registrations";
                }
            }
        }
    };

    var chartRegistrations = new ApexCharts(overviewElement, optionsOverview);
    chartRegistrations.render();
    

    const overviewElement2 = document.querySelector("#chart-hourly-registrations");
        const registrationHourDates = JSON.parse(overviewElement2.dataset.dates);
        const registrationHourHours = JSON.parse(overviewElement2.dataset.hours);
        const registrationHourSeries = JSON.parse(overviewElement2.dataset.counts);


        // Hourly Customer Registrations Chart
        var optionsHourly = {
        series: registrationHourSeries,
        chart: {
            type: 'bar',
            height: 300,
            toolbar: { show: false }
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '70%',
                endingShape: 'rounded',
                 dataLabels: {
                    position: 'top'
                }
            },
           
        },
        dataLabels: { 
            enabled: true,
            style: {
                fontSize: '12px',
                fontWeight: 'bold',
                colors: ['#00000']
            },
            background: {
                enabled: false,
                foreColor: '#fff',
                borderRadius: 2,
                padding: 4,
                opacity: 0.9,
                borderWidth: 1,
                borderColor: '#206bc4'
            },
            formatter: function (val) {
                return val === 0 ? '' : val; // hide label if value is 0
            }
        },
        stroke: {
            show: true,
            width: 3,
            colors: ['transparent']
        },
        xaxis: { categories: registrationHourHours },
        yaxis: {
            title: {
                text: 'Registrations by Hour'
            }
        },
        fill: { opacity: 1 },
        tooltip: {
            y: {
                formatter: function(val) {
                    return val + " registrations by hour";
                }
            }
        }
    };
    var chartHourly = new ApexCharts(document.querySelector("#chart-hourly-registrations"), optionsHourly);
    chartHourly.render();

    // Store chart instances globally for export
    window.chartRegistrations = chartRegistrations;
    window.chartHourly = chartHourly;

    // Make exportChart function globally available
    window.exportChart = exportChart;
});

// Export chart function
function exportChart(chartId, format) {
    const chart = chartId === 'chart-customers-overview' ? window.chartRegistrations : window.chartHourly;
    const filename = chartId === 'chart-customers-overview' ? 'customer-registrations' : 'hourly-registrations';

    if (!chart) {
        console.error('Chart not found:', chartId);
        return;
    }

    switch(format) {
        case 'csv':
            chart.dataURI().then(({ imgURI, blob }) => {
                // For CSV, we need to manually export the data
                const series = chart.w.config.series;
                const categories = chart.w.config.xaxis.categories;
                
                let csv = 'Category,Value\n';
                categories.forEach((category, index) => {
                    csv += `"${category}",${series[0].data[index]}\n`;
                });
                
                const csvBlob = new Blob([csv], { type: 'text/csv' });
                const url = window.URL.createObjectURL(csvBlob);
                const link = document.createElement('a');
                link.href = url;
                link.download = filename + '.csv';
                link.click();
                window.URL.revokeObjectURL(url);
            });
            break;
            
        case 'png':
            chart.dataURI().then(({ imgURI }) => {
                const link = document.createElement('a');
                link.href = imgURI;
                link.download = filename + '.png';
                link.click();
            });
            break;
            
        case 'svg':
            chart.dataURI({ type: 'svg' }).then(({ imgURI }) => {
                const link = document.createElement('a');
                link.href = imgURI;
                link.download = filename + '.svg';
                link.click();
            });
            break;
    }
}
