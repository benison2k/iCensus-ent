import { drawChart } from './chartManager.js';
import { fetchData } from './api.js';

const basePath = '/iCensus-ent/public';
let grid;
const chartsToDraw = new Map();

function redrawChartOnResize(el) {
    if (!el || !el.gridstackNode) return;
    const chartId = el.gridstackNode.id;
    const chartDiv = document.getElementById(`chart_div_${chartId}`);
    if (chartDiv && chartDiv.chartInstance && chartDiv.chartData && chartDiv.chartOptions) {
        const chartType = chartDiv.chartType;
        // Use the correct Google Charts method for Bar/Column charts on resize
        if (chartType === 'BarChart' || chartType === 'ColumnChart') {
            chartDiv.chartInstance.draw(chartDiv.chartData, chartDiv.chartOptions);
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
        resizable: { handles: 'n, e, s, w, ne, nw, se, sw' }
    });

    grid.on('added', (event, items) => {
        items.forEach(item => {
            const chartId = item.id;
            if (chartsToDraw.has(chartId)) {
                const chartDef = chartsToDraw.get(chartId);
                // The identifier for drawing can be the new int ID or old string metric_id
                drawChart(chartDef.identifier, chartDef.title, chartDef.type);
                chartsToDraw.delete(chartId);
            }
        });
    });

    grid.on('resizestop', (event, el) => redrawChartOnResize(el));
    window.addEventListener('resize', () => { 
        if (grid?.engine?.nodes) {
            grid.engine.nodes.forEach(node => redrawChartOnResize(node.el));
        }
    });
}

export function addWidgetToGrid(chartId, chartTitle, chartType) {
    const chartIcon = 'donut_large'; 
    const widgetHtml = `
        <div class="grid-stack-item-content chart-container">
            <div class="chart-title"><span class="material-icons chart-icon">${chartIcon}</span>${chartTitle}</div>
            <div class="chart-div" id="chart_div_${chartId}"></div>
        </div>`;
    
    grid.addWidget(widgetHtml, { w: 4, h: 4, autoPosition: true, id: chartId });
    drawChart(chartId, chartTitle, chartType);
}

export async function loadLayout() {
    const layoutData = await fetchData('analytics/layout');
    const chartsResult = await fetchData('analytics/charts');
    
    if (!chartsResult.charts) return;

    // Create a map that can look up a chart by EITHER its new integer ID or its old string ID
    const chartMap = new Map();
    chartsResult.charts.forEach(c => {
        chartMap.set(c.id.toString(), c); // Map by new integer ID
        if (c.metric_id) {
            chartMap.set(c.metric_id, c); // Also map by old string ID
        }
    });

    grid.removeAll(); 

    if (layoutData && layoutData.length > 0) {
        layoutData.forEach(node => {
            const chartDef = chartMap.get(node.id); // Look up using the ID from the layout (string or int)
            
            if (chartDef) {
                // The identifier used to fetch data (can be int or string)
                const identifier = chartDef.metric_id || chartDef.id;
                // The ID for the grid widget MUST be unique. We'll use the primary key.
                const widgetId = chartDef.id;

                chartsToDraw.set(widgetId, { identifier: identifier, title: chartDef.title, type: chartDef.chart_type });

                const widgetHtml = `
                    <div class="grid-stack-item-content chart-container">
                        <div class="chart-title"><span class="material-icons chart-icon">donut_large</span>${chartDef.title}</div>
                        <div class="chart-div" id="chart_div_${widgetId}"></div>
                    </div>`;
                
                // Important: Use the new integer ID for the grid node to avoid conflicts
                node.id = widgetId;
                grid.addWidget(widgetHtml, node);
            }
        });
    }
}

export function saveLayout() {
    const serializedData = grid.save(true, true).children;
    const layout = serializedData.map(d => ({
        id: d.id, // This will now correctly save the integer ID
        x: d.x, y: d.y, w: d.w, h: d.h
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
