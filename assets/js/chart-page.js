
    const availableColors = [
        'rgb(255, 99, 132)',
        'rgb(255, 159, 64)',
        'rgb(255, 205, 86)',
        'rgb(180, 195, 90)',
        'rgb(75, 192, 192)',
        'rgb(54, 162, 235)',
        'rgb(153, 102, 255)'
    ];

    document.addEventListener("DOMContentLoaded", () => {

        const ctx = document.getElementById('mpr-chart'),
              rangeObj = document.getElementById('mpr-chart-filter-period'),
              periodObj = document.getElementById('mpr-stat-value');

        if (ctx && rangeObj && periodObj) {

            const loader = document.getElementById('mpr-loader');

            wp.apiRequest({
                path: 'mpr/v1/get-statistic',
                method: 'GET',
                data: {
                    'range'  : rangeObj.value,
                    'period' : periodObj.value,
                }
            }).done(data => {
                if ( data.success ) {
                    initChart(ctx, data.data);
                    loader.style.display = 'none';
                } else {
                    loader.classList.add('error');
                }
            }).fail((request, statusText) => {
                loader.classList.add('error');
            });

            rangeObj.addEventListener("change", (event) => {
                document.getElementById("mpr-chart-filter-form").submit();
            });
        }
    });

    function initChart(ctx, data)
    {
        if ( ! data.totals[0] ) {
            return;
        }

        let dataSets = [
            {
            label: 'Total',
            data: Object.values(data.totals[0]),
            }
        ];

        data.lines.forEach((item) => {
            if ( ! item.data[0] ) {
                return;
            }

            let itemColor = '';
            if ( availableColors.length ) {
                itemColor = availableColors.pop();
            }

            dataSets.push({
                type: 'line',
                label: item.label,
                data: Object.values(item.data[0]),
                borderColor: itemColor,
                backgroundColor: itemColor,
                tension: 0.1
            });
        });

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: Object.keys(data.totals[0]),
                datasets: dataSets,
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
