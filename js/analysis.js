document.addEventListener('DOMContentLoaded', function() {
    fetchSurveyData();
});

function fetchSurveyData() {
    fetch('php/analysis.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            document.getElementById('chart-loader').style.display = 'none';
            updateDashboard(data);
        })
        .catch(error => {
            document.getElementById('chart-loader').style.display = 'none';
            console.error('Error fetching survey data:', error);
            document.querySelector('.chart-container').innerHTML = 
                '<div class="error-message">Failed to load survey data. Please check your connection and try again.</div>';
        });
}

function updateDashboard(data) {
    document.getElementById('dts-count').textContent = data.pending.dts;
    document.getElementById('email-count').textContent = data.pending.email;
    document.getElementById('help-count').textContent = data.pending.help;
    document.getElementById('ict-count').textContent = data.pending.ict;

    const currentQuarter = getCurrentQuarter();
    
    const lineCtx = document.getElementById('TicketLineChart').getContext('2d');
    new Chart(lineCtx, {
        type: 'line',
        data: {
            labels: ['Q1 (Jan-Mar)', 'Q2 (Apr-Jun)', 'Q3 (Jul-Sep)', 'Q4 (Oct-Dec)'],
            datasets: [
                {
                    label: 'DTS Request',
                    data: data.quarterly.dts,
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    tension: 0.3,
                    fill: true
                },
                {
                    label: 'Email Request',
                    data: data.quarterly.email,
                    borderColor: 'rgba(255, 99, 132, 1)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    tension: 0.3,
                    fill: true
                },
                {
                    label: 'Help Desk',
                    data: data.quarterly.help,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.3,
                    fill: true
                },
                {
                    label: 'ICT Assistance',
                    data: data.quarterly.ict,
                    borderColor: 'rgba(255, 159, 64, 1)',
                    backgroundColor: 'rgba(255, 159, 64, 0.2)',
                    tension: 0.3,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
                title: { 
                    display: true, 
                    text: `Quarterly Ticket Submissions (Current: ${currentQuarter})`, 
                    font: { size: 16 } 
                },
                tooltip: {
                    callbacks: {
                        title: function(context) {
                            return context[0].label;
                        },
                        label: function(context) {
                            return `${context.dataset.label}: ${context.raw} submissions`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Submissions'
                    },
                    ticks: {
                        precision: 0
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Quarters'
                    }
                }
            }
        }
    });
}

function getCurrentQuarter() {
    const month = new Date().getMonth();
    if (month < 3) return "Q1 (Jan-Mar)";
    if (month < 6) return "Q2 (Apr-Jun)";
    if (month < 9) return "Q3 (Jul-Sep)";
    return "Q4 (Oct-Dec)";
}