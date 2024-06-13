const data = {
    labels: [
        'Tests OK',
        'Tests KO',
        'Non testés'
    ],
    datasets: [{
        label: 'Résultats de la campagne',
        data: [document.currentScript.getAttribute('data-campagne-nb-ok'), document.currentScript.getAttribute('data-campagne-nb-ko'), document.currentScript.getAttribute('data-campagne-nb-nt')],
        backgroundColor: [
            'rgba(192, 248, 196, 0.5)',
            'rgba(244, 170, 170, 0.5)',
            'rgba(39, 40, 40, 0.18)'
        ],
        hoverOffset: 4
    }]
};

const config = {
    type: 'doughnut',
    data: data,
};

var myChart = new Chart(
    document.getElementById('myChart'),
    config
);