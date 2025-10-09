import { fetchData } from './api.js';

export async function drawChart(chartId, chartTitle, chartType) {
    const chartDiv = document.getElementById(`chart_div_${chartId}`);
    if (!chartDiv) return;

    // Fetch the chart's data using the dynamic endpoint with the correct integer ID
    const apiData = await fetchData('analytics/dynamic-data', { chart_id: chartId });

    if (apiData.error) {
        chartDiv.innerHTML = `<div class="chart-error">Error: ${apiData.error}</div>`;
        return;
    }

    // Store the chart type on the DOM element for resizing logic
    chartDiv.chartType = chartType;

    if (chartType === 'KPI') {
        chartDiv.innerHTML = `<div class="kpi-value">${apiData.value}</div><div class="kpi-label">${apiData.label || ''}</div>`;
        return;
    }

    const dataArray = [['Category', 'Count']];
    for (const key in apiData) {
        dataArray.push([key, apiData[key]]);
    }
    const data = google.visualization.arrayToDataTable(dataArray);
    chartDiv.chartData = data; // Store for resizing

    const isDarkMode = document.body.classList.contains('dark-mode');
    const fontColor = isDarkMode ? '#CFD8DC' : '#333';
    
    const options = {
        title: '',
        width: '100%', height: '100%',
        backgroundColor: 'transparent',
        chartArea: { 'width': '85%', 'height': '70%' },
        legend: { position: 'bottom', textStyle: { color: fontColor } },
        hAxis: { textStyle: { color: fontColor }, titleTextStyle: { color: fontColor } },
        vAxis: { textStyle: { color: fontColor }, titleTextStyle: { color: fontColor } }
    };
    chartDiv.chartOptions = options; // Store for resizing

    let chart;
    switch (chartType) {
        case 'BarChart':
            chart = new google.visualization.BarChart(chartDiv);
            break;
        case 'ColumnChart':
            chart = new google.visualization.ColumnChart(chartDiv);
            break;
        case 'PieChart':
        default:
            options.pieHole = 0.4; // Make it a donut chart by default
            chart = new google.visualization.PieChart(chartDiv);
            break;
    }
    chartDiv.chartInstance = chart; // Store for resizing

    chart.draw(data, options);
}
