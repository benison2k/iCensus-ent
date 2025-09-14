google.charts.load('current', {'packages':['corechart']});
google.charts.setOnLoadCallback(drawCharts);

function drawCharts() {
    fetch('../core/analytics_data.php')
        .then(response => response.json())
        .then(data => {
            drawGenderChart(data.gender);
            drawAgeChart(data.age);
            drawStatusChart(data.status);
            drawPurokChart(data.purok);
            drawBarangayChart(data.barangay);
            drawCivilStatusChart(data.civil_status);
            drawBloodTypeChart(data.blood_type);
            drawResidencyStatusChart(data.residency_status);
        });
}

function drawGenderChart(genderData) {
    const data = google.visualization.arrayToDataTable([
        ['Gender', 'Count'],
        ['Male', genderData.Male],
        ['Female', genderData.Female],
        ['Other', genderData.Other]
    ]);

    const options = {
        title: 'Gender Distribution',
        pieHole: 0.4,
    };

    const chart = new google.visualization.PieChart(document.getElementById('gender_chart_div'));
    chart.draw(data, options);
}

function drawAgeChart(ageData) {
    const data = google.visualization.arrayToDataTable([
        ['Age Group', 'Count', { role: 'style' }],
        ['0-17', ageData['0-17'], '#3366cc'],
        ['18-35', ageData['18-35'], '#dc3912'],
        ['36-59', ageData['36-59'], '#ff9900'],
        ['60+', ageData['60+'], '#109618']
    ]);

    const options = {
        title: 'Age Distribution',
        legend: { position: 'none' }
    };

    const chart = new google.visualization.BarChart(document.getElementById('age_chart_div'));
    chart.draw(data, options);
}

function drawStatusChart(statusData) {
    const dataArray = [['Status', 'Count']];
    for (const status in statusData) {
        dataArray.push([status, statusData[status]]);
    }
    const data = google.visualization.arrayToDataTable(dataArray);

    const options = {
        title: 'Resident Status (Active, Inactive, etc.)',
    };

    const chart = new google.visualization.PieChart(document.getElementById('status_chart_div'));
    chart.draw(data, options);
}

function drawPurokChart(purokData) {
    const dataArray = [['Purok', 'Count']];
    for (const purok in purokData) {
        dataArray.push([`Purok ${purok}`, purokData[purok]]);
    }
    const data = google.visualization.arrayToDataTable(dataArray);

    const options = {
        title: 'Population by Purok',
        legend: { position: 'none' }
    };

    const chart = new google.visualization.ColumnChart(document.getElementById('purok_chart_div'));
    chart.draw(data, options);
}

function drawBarangayChart(barangayData) {
    const dataArray = [['Barangay', 'Count']];
    for (const barangay in barangayData) {
        dataArray.push([barangay, barangayData[barangay]]);
    }
    const data = google.visualization.arrayToDataTable(dataArray);

    const options = {
        title: 'Population by Barangay',
        legend: { position: 'none' }
    };

    const chart = new google.visualization.ColumnChart(document.getElementById('barangay_chart_div'));
    chart.draw(data, options);
}

function drawCivilStatusChart(civilStatusData) {
    const dataArray = [['Civil Status', 'Count']];
    for (const status in civilStatusData) {
        dataArray.push([status, civilStatusData[status]]);
    }
    const data = google.visualization.arrayToDataTable(dataArray);

    const options = {
        title: 'Civil Status Distribution',
        pieHole: 0.4,
    };

    const chart = new google.visualization.DoughnutChart(document.getElementById('civil_status_chart_div'));
    chart.draw(data, options);
}

function drawBloodTypeChart(bloodTypeData) {
    const dataArray = [['Blood Type', 'Count']];
    for (const type in bloodTypeData) {
        dataArray.push([type, bloodTypeData[type]]);
    }
    const data = google.visualization.arrayToDataTable(dataArray);

    const options = {
        title: 'Blood Type Distribution',
    };

    const chart = new google.visualization.PieChart(document.getElementById('blood_type_chart_div'));
    chart.draw(data, options);
}

function drawResidencyStatusChart(residencyStatusData) {
    const dataArray = [['Residency Status', 'Count']];
    for (const status in residencyStatusData) {
        dataArray.push([status, residencyStatusData[status]]);
    }
    const data = google.visualization.arrayToDataTable(dataArray);

    const options = {
        title: 'Residency Status (Resident, Non-resident)',
        legend: { position: 'none' }
    };

    const chart = new google.visualization.BarChart(document.getElementById('residency_status_chart_div'));
    chart.draw(data, options);
}