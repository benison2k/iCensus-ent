// Make the chart drawing function global so the grid can access it
let chartDataCache = null; 
window.drawAllCharts = () => {
    if (!chartDataCache) return;

    const isDarkMode = document.body.classList.contains('dark-mode');
    const themeOptions = getThemeOptions(isDarkMode);

    drawPieChart('gender_chart_div', 'Population by Gender', chartDataCache.gender, themeOptions);
    drawBarChart('age_group_chart_div', 'Population by Age Group', chartDataCache.age_groups, themeOptions);
    drawColumnChart('civil_status_chart_div', 'Civil Status Distribution', chartDataCache.civil_status, themeOptions);
    drawPieChart('purok_chart_div', 'Residents by Purok', chartDataCache.purok, themeOptions, true);
    drawDonutChart('blood_type_chart_div', 'Blood Type Distribution', chartDataCache.blood_type, themeOptions);
    drawPieChart('status_chart_div', 'Resident Status', chartDataCache.status, themeOptions);
};


document.addEventListener('DOMContentLoaded', () => {
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(fetchAndDrawCharts);

    function fetchAndDrawCharts() {
        fetch('../core/analytics_data.php')
            .then(response => {
                if (!response.ok) { throw new Error('Network response was not ok'); }
                return response.json();
            })
            .then(data => {
                if (data.error) { throw new Error(data.error); }
                chartDataCache = data; // Cache the data
                window.drawAllCharts(); // Initial draw
            })
            .catch(error => {
                console.error('Error fetching analytics data:', error);
                document.querySelector('.analytics-dashboard').innerHTML = `<p style="color: red; text-align: center;">Error loading chart data: ${error.message}</p>`;
            });
    }
});

function getThemeOptions(isDarkMode) {
    const fontColor = isDarkMode ? '#ffffff' : '#333';
    const legendColor = isDarkMode ? '#eeeeee' : '#555';
    const bgColor = isDarkMode ? '#283747' : '#ffffff';
    
    return {
        backgroundColor: bgColor,
        titleTextStyle: { color: fontColor },
        legendTextStyle: { color: legendColor },
        hAxis: { textStyle: { color: legendColor }, titleTextStyle: { color: fontColor } },
        vAxis: { textStyle: { color: legendColor }, titleTextStyle: { color: fontColor } }
    };
}

function drawPieChart(elementId, title, data, themeOptions, is3D = false) {
    const dataTable = google.visualization.arrayToDataTable(data);
    const options = { ...themeOptions, title: title, is3D: is3D };
    const chart = new google.visualization.PieChart(document.getElementById(elementId));
    chart.draw(dataTable, options);
}

function drawDonutChart(elementId, title, data, themeOptions) {
    const dataTable = google.visualization.arrayToDataTable(data);
    const options = { ...themeOptions, title: title, pieHole: 0.4 };
    const chart = new google.visualization.PieChart(document.getElementById(elementId));
    chart.draw(dataTable, options);
}

function drawBarChart(elementId, title, data, themeOptions) {
    const dataTable = google.visualization.arrayToDataTable(data);
    const options = { ...themeOptions, title: title, chartArea: { width: '50%' } };
    const chart = new google.visualization.BarChart(document.getElementById(elementId));
    chart.draw(dataTable, options);
}

function drawColumnChart(elementId, title, data, themeOptions) {
    const dataTable = google.visualization.arrayToDataTable(data);
    const options = { ...themeOptions, title: title };
    const chart = new google.visualization.ColumnChart(document.getElementById(elementId));
    chart.draw(dataTable, options);
}