// public/assets/js/gridManager.js

import { drawChart } from './chartManager.js';
import { getChartInfo } from './chartConfig.js'; // Assuming you export this from chartConfig

let grid;
const chartsToDraw = {};

function redrawChartOnResize(el) {
    // Your existing redrawChartOnResize logic
}

export function initializeGrid() {
    grid = GridStack.init({ /* ... your grid options ... */ });

    // Move all your grid.on() event listeners here
    grid.on('added', (event, items) => {
        items.forEach(item => {
            const metric = item.id;
            if (chartsToDraw[metric]) {
                chartsToDraw[metric](); // This will be `drawChart(metric)`
                delete chartsToDraw[metric];
            }
            if (getChartInfo(metric).type !== 'KPI' && item.el) {
                // The click to open detail modal logic
            }
        });
    });

    grid.on('resizestop', (event, el) => redrawChartOnResize(el));
}

export function loadLayout() {
    // Your existing loadLayout logic using fetchData
}

export function saveLayout() {
    // Your existing saveLayout logic
}

export function resetLayout() {
    // Your existing resetLayout logic
}