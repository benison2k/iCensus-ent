// public/assets/js/chartManager.js

import { getChartInfo } from './chartConfig.js';
import { fetchData } from './api.js';

// NEW: Module-level variables to store the last applied date filter state
let currentStartDate = null;
let currentEndDate = null;

/**
 * Generates URLSearchParams for resident filtering.
 * FIX: This function now accepts dates and uses the stored state as a fallback.
 */
export function getFilterParamForMetric(metric, category, series = null, startDate = null, endDate = null) {
    let params = new URLSearchParams();
    const cleanCategory = category.split(' = ')[0];

    // Use the passed dates (from the modal click), but fall back to the stored state
    // This guarantees the date filter is always applied if it was used for the chart.
    const effectiveStartDate = startDate || currentStartDate;
    const effectiveEndDate = endDate || currentEndDate;

    // Add the effective global date filters to the parameters
    if (effectiveStartDate) params.set('start_date', effectiveStartDate);
    if (effectiveEndDate) params.set('end_date', effectiveEndDate);

    switch (metric) {
        case 'gender':
        case 'civil_status':
        case 'purok':
        case 'blood_type':
        case 'nationality':
        case 'occupation':
        case 'educational_attainment':
        case 'ownership_status':
        case 'relationship':
        case 'resident_status_overview':
        case 'residents_per_street':
            const paramKey = (metric === 'resident_status_overview') ? 'status' : (metric === 'residents_per_street' ? 'street' : metric.replace('_status', ''));
            params.set(paramKey, cleanCategory);
            break;
        case 'pwd_distribution':
            params.set('is_pwd', cleanCategory.toLowerCase() === 'yes' ? '1' : '0');
            break;
        case 'solo_parent_distribution':
            params.set('is_solo_parent', cleanCategory.toLowerCase() === 'yes' ? '1' : '0');
            break;
        case '4ps_distribution':
            params.set('is_4ps_member', cleanCategory.toLowerCase() === 'yes' ? '1' : '0');
            break;
        case 'age':
        case 'detailed_age_brackets':
            const ages = cleanCategory.match(/\d+/g);
            if (ages) {
                params.set('age_min', ages[0]);
                if (ages.length > 1) params.set('age_max', ages[1]);
            }
            break;
        case 'population_pyramid':
            const ageBrackets = cleanCategory.match(/\d+/g);
            if (ageBrackets) {
                params.set('age_min', ageBrackets[0]);
                if (ageBrackets.length > 1) params.set('age_max', ageBrackets[1]);
            }
            if (series) params.set('gender', series);
            break;
        case 'generation_breakdown':
            params.set('generation', cleanCategory);
            break;
        case 'sex_ratio':
            params.set('gender', cleanCategory);
            break;
        case 'heads_of_household_by_gender':
            params.set('gender', cleanCategory);
            params.set('is_head', 'Yes');
            break;
        case 'voter_population_by_purok':
            params.set('purok', cleanCategory);
            params.set('is_voter', '1');
            break;
        case 'senior_citizens_by_purok':
            params.set('purok', cleanCategory);
            params.set('age_min', '60');
            break;
        case 'emergency_contact_coverage':
            params.set('has_emergency_contact', cleanCategory.startsWith('Has') ? 'Yes' : 'No');
            break;
        case 'civil_status_distribution_by_gender':
            params.set('civil_status', cleanCategory);
            if(series) params.set('gender', series);
            break;
        case 'household_size_distribution':
            const size = cleanCategory.match(/\d+/);
            if (size) params.set('household_size', size[0] + (cleanCategory.includes('+') ? '+' : ''));
            break;
        case 'school_age_population_by_purok':
             params.set('purok', cleanCategory);
             if (series) {
                const ageGroup = series.match(/\((\d+)-(\d+)\)/);
                if (ageGroup) {
                    params.set('age_min', ageGroup[1]);
                    params.set('age_max', ageGroup[2]);
                }
            }
            break;
        case 'profile_completeness':
            const fieldMap = { 'Contact Info': 'contact_number', 'Email': 'email', 'Emergency Contact': 'emergency_name', 'Blood Type': 'blood_type'};
            const field = fieldMap[cleanCategory];
            if(field) params.set('has_field', field);
            break;
        default:
            // Return params even if only dates are set
            if (params.toString() !== '') return params; 
            return null;
    }
    return params;
}

/**
 * Draws the chart and updates the date state.
 * FIX: This function now accepts dates and stores them.
 */
export async function drawChart(metric, startDate = null, endDate = null) {
    const chartDiv = document.getElementById(`${metric}_chart_div`);
    const chartInfo = getChartInfo(metric);

    // Store the dates used for drawing the chart in the module-level state
    currentStartDate = startDate;
    currentEndDate = endDate;

    const apiParams = { metric };
    if (startDate) apiParams.start_date = startDate;
    if (endDate) apiParams.end_date = endDate;

    const apiData = await fetchData('analytics/data', apiParams);

    if (!chartDiv || apiData.error) {
        if (chartDiv) chartDiv.innerHTML = `<div class="chart-error">Error: ${apiData.error || 'No data'}</div>`;
        return;
    }

    if (chartInfo.type === 'KPI') {
        chartDiv.innerHTML = `<div class="kpi-value">${apiData.value}</div><div class="kpi-label">${apiData.label || ''}</div>`;
        return;
    }

    const isDarkMode = document.body.classList.contains('dark-mode');
    const fontColor = isDarkMode ? '#CFD8DC' : '#333';

    let data;
    let options = {
        title: '',
        width: '100%',
        height: '100%',
        backgroundColor: 'transparent',
        chartArea: { 'width': '85%', 'height': '70%' },
        legend: { position: 'bottom', textStyle: { color: fontColor } },
        hAxis: { textStyle: { color: fontColor }, titleTextStyle: { color: fontColor } },
        vAxis: { textStyle: { color: fontColor }, titleTextStyle: { color: fontColor } }
    };

    if (chartInfo.type === 'PopulationPyramid') {
        const pyramidData = [['Age', 'Male', { role: 'style' }, 'Female', { role: 'style' }]];
        for (const age in apiData) {
            pyramidData.push([age, Math.abs(apiData[age]['Male'] || 0), 'color: #3366cc', Math.abs(apiData[age]['Female'] || 0), 'color: #ffc0cb']);
        }
        data = google.visualization.arrayToDataTable(pyramidData);
        options.isStacked = false;
        options.hAxis.title = 'Population';
    } else if (chartInfo.type === 'GroupedBar') {
        const categories = Object.keys(apiData);
        if (categories.length === 0) return;
        const firstCategoryData = apiData[categories[0]];
        const groups = Object.keys(firstCategoryData);
        const dataArray = [[chartInfo.title, ...groups]];
        for (const category in apiData) {
            const row = [category];
            groups.forEach(group => row.push(apiData[category][group] || 0));
            dataArray.push(row);
        }
        data = google.visualization.arrayToDataTable(dataArray);
        options.hAxis.title = '';
    } else {
        const dataArray = [[chartInfo.title, 'Count']];
        for (const key in apiData) {
            dataArray.push([key, apiData[key]]);
        }
        data = google.visualization.arrayToDataTable(dataArray);
        if (metric === 'gender' || metric === 'civil_status' || metric === 'sex_ratio') options.pieHole = 0.4;
    }

    chartDiv.chartData = data;
    chartDiv.chartOptions = options;

    let chart;
    if (chartInfo.type === 'PopulationPyramid' || chartInfo.type === 'GroupedBar') chart = new google.charts.Bar(chartDiv);
    else if (chartInfo.type === 'ColumnChart') chart = new google.visualization.ColumnChart(chartDiv);
    else if (chartInfo.type === 'BarChart') chart = new google.visualization.BarChart(chartDiv);
    else chart = new google.visualization.PieChart(chartDiv);

    chartDiv.chartInstance = chart;

    if (chartInfo.type === 'PopulationPyramid' || chartInfo.type === 'GroupedBar') {
        chart.draw(data, google.charts.Bar.convertOptions(options));
    } else {
        chart.draw(data, options);
    }
}