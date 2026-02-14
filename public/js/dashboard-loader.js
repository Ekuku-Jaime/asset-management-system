// Configurações padrão para os gráficos do dashboard
const chartConfigs = {
    colors: {
        primary: '#3b82f6',
        success: '#10b981',
        warning: '#f59e0b',
        danger: '#ef4444',
        info: '#06b6d4',
        dark: '#6b7280'
    },
    
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 20,
                    usePointStyle: true
                }
            },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                padding: 12,
                titleFont: {
                    size: 14
                },
                bodyFont: {
                    size: 13
                }
            }
        }
    },
    
    // Configurações específicas para cada tipo de gráfico
    doughnut: {
        cutout: '70%',
        plugins: {
            legend: {
                position: 'right'
            }
        }
    },
    
    bar: {
        barPercentage: 0.7,
        categoryPercentage: 0.8
    },
    
    line: {
        tension: 0.4,
        pointRadius: 4,
        pointHoverRadius: 6
    }
};

// Funções auxiliares para formatação
const formatters = {
    currency: (value) => {
        return new Intl.NumberFormat('pt-PT', {
            style: 'currency',
            currency: 'MZN',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(value);
    },
    
    number: (value) => {
        return new Intl.NumberFormat('pt-PT').format(value);
    },
    
    percent: (value) => {
        return `${value.toFixed(1)}%`;
    },
    
    date: (dateString) => {
        return new Date(dateString).toLocaleDateString('pt-PT');
    }
};