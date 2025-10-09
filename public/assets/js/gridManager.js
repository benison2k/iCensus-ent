// public/assets/js/gridManager.js

import { drawChart } from './chartManager.js';
import { getChartInfo } from './chartConfig.js';
import { showDetailModal } from './modalManager.js';

const basePath = '/iCensus-ent/public';
let grid;
const chartsToDraw = {};

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function redrawChartOnResize(el) {
    if (!el || !el.gridstackNode) return;
    const id = el.gridstackNode.id;
    const chartDiv = document.getElementById(`${id}_chart_div`);
    if (chartDiv && chartDiv.chartInstance && chartDiv.chartData && chartDiv.chartOptions) {
        const chartInfo = getChartInfo(id);
        if (chartInfo.type === 'PopulationPyramid' || chartInfo.type === 'GroupedBar') {
            chartDiv.chartInstance.draw(chartDiv.chartData, google.charts.Bar.convertOptions(chartDiv.chartOptions));
        } else {
            chartDiv.chartInstance.draw(chartDiv.chartData, chartDiv.chartOptions);
        }
    }
}

export function initializeGrid() {
    grid = GridStack.init({
        cellHeight: 80,
        margin: 20,
        float: true,
        resizable: {
            handles: 'n, e, s, w, ne, nw, se, sw'
        }
    });

    grid.on('added', (event, items) => {
        items.forEach(item => {
            const metric = item.id;
            if (chartsToDraw[metric]) {
                chartsToDraw[metric]();
                delete chartsToDraw[metric];
            }
            if (getChartInfo(metric).type !== 'KPI' && item.el) {
                item.el.addEventListener('click', () => showDetailModal(metric));
            }
        });
    });

    grid.on('resizestop', (event, el) => redrawChartOnResize(el));

    window.addEventListener('resize', debounce(() => {
        if (grid && grid.engine && grid.engine.nodes) {
            grid.engine.nodes.forEach(node => redrawChartOnResize(node.el));
        }
    }, 250));
}

export function loadLayout() {
    // Read the date values from the input fields
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;

    fetch(`${basePath}/analytics/layout`)
        .then(response => response.json())
        .then(layoutData => {
            if (!layoutData || layoutData.length === 0) return;
            grid.removeAll();
            layoutData.forEach(node => {
                // Pass the dates when scheduling the chart to be drawn
                chartsToDraw[node.id] = () => drawChart(node.id, startDate, endDate);
                const chartInfo = getChartInfo(node.id);
                const isKpi = chartInfo.type === 'KPI';
                const contentHtml = isKpi ? `<div class="kpi-content" id="${node.id}_chart_div"></div>` : `<div class="chart-div" id="${node.id}_chart_div"></div>`;
                const widgetHtml = `
                    <div class="grid-stack-item-content chart-container" data-metric="${node.id}">
                        <div class="chart-title"><span class="material-icons chart-icon">${chartInfo.icon}</span>${chartInfo.title}</div>
                        ${contentHtml}
                    </div>`;
                grid.addWidget(widgetHtml, node);
            });
        });
}

export function saveLayout() {
    const serializedData = grid.save(true, true).children;
    const layout = serializedData.map(d => ({
        id: d.id, x: d.x, y: d.y, w: d.w, h: d.h
    }));
    fetch(`${basePath}/analytics/layout/save`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(layout)
    })
    .then(res => res.json()).then(result => alert(result.status === 'success' ? 'Layout saved!' : 'Error saving layout.'));
}

export function resetLayout() {
    if (confirm('Are you sure you want to reset your layout to the default?')) {
        fetch(`${basePath}/analytics/layout/reset`, { method: 'POST' })
            .then(res => res.json()).then(result => {
                if (result.status === 'success') loadLayout();
            });
    }
}