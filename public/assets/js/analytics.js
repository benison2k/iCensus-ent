// --- Report Modal Logic (Runs Immediately) ---
document.addEventListener('DOMContentLoaded', () => {
    const reportModal = document.getElementById('report-modal');
    const generateReportBtn = document.getElementById('generate-report-btn');
    
    if (reportModal && generateReportBtn) {
        const closeBtn = reportModal.querySelector('.close-btn');
        generateReportBtn.addEventListener('click', () => {
            reportModal.style.display = 'block';
        });
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                reportModal.style.display = 'none';
            });
        }
        window.addEventListener('click', (event) => {
            if (event.target == reportModal) {
                reportModal.style.display = 'none';
            }
        });
    }

    const detailModal = document.getElementById('chart-detail-modal');
    if (detailModal) {
        const closeBtn = detailModal.querySelector('.close-btn');
        closeBtn.addEventListener('click', () => {
            detailModal.style.display = 'none';
        });
        window.addEventListener('click', (event) => {
            if (event.target == detailModal) {
                detailModal.style.display = 'none';
            }
        });
    }
});


// --- Google Charts and GridStack Logic ---
google.charts.load('current', {'packages':['corechart', 'bar']});
google.charts.setOnLoadCallback(initializeDashboard);

let grid;
let chartsToDraw = {};
const basePath = '/icensus-ent/iCensus-ent-overhaul-MVC-file-structure-implementation-/public';

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

function initializeDashboard() {
    grid = GridStack.init({
        cellHeight: 80,
        margin: 30,
        float: true,
        resizable: { handles: 'n, e, s, w, ne, nw, se, sw' }
    });

    loadLayout();

    grid.on('added', function(event, items) {
        items.forEach(item => {
            const chartId = item.id;
            if (chartsToDraw[chartId]) {
                chartsToDraw[chartId]();
                delete chartsToDraw[chartId];
            }
        });
    });

    const redrawChart = (el) => {
        const id = el.gridstackNode.id;
        const chartDiv = document.getElementById(`${id}_chart_div`);
        if (chartDiv && chartDiv.chartInstance && chartDiv.chartData && chartDiv.chartOptions) {
             if (chartDiv.chartType && (chartDiv.chartType === 'GroupedBar' || chartDiv.chartType === 'PopulationPyramid')) {
                chartDiv.chartInstance.draw(chartDiv.chartData, google.charts.Bar.convertOptions(chartDiv.chartOptions));
            } else {
                chartDiv.chartInstance.draw(chartDiv.chartData, chartDiv.chartOptions);
            }
        }
    };

    grid.on('resizestop', (event, el) => redrawChart(el));
    window.addEventListener('resize', debounce(() => {
        if(grid && grid.engine && grid.engine.nodes) {
            grid.engine.nodes.forEach(node => redrawChart(node.el));
        }
    }, 250));

    document.getElementById('save-layout-btn').addEventListener('click', saveLayout);
    document.getElementById('reset-layout-btn').addEventListener('click', resetLayout);
}

function loadLayout() {
    // --- URL UPDATED HERE ---
    fetch(`${basePath}/analytics/layout`)
        .then(response => response.json())
        .then(layoutData => {
            grid.removeAll();
            layoutData.forEach(node => {
                chartsToDraw[node.id] = () => drawChart(node.id);
                const isKpi = getChartType(node.id) === 'KPI';
                const contentHtml = isKpi ?
                    `<div class="kpi-content" id="${node.id}_chart_div">Loading...</div>` :
                    `<div class="chart-div" id="${node.id}_chart_div">Loading...</div>`;

                const widgetHtml = `
                    <div class="grid-stack-item-content chart-container" data-metric="${node.id}">
                        <div class="chart-title">
                            <span class="material-icons chart-icon">${getChartIcon(node.id)}</span>
                            ${getChartTitle(node.id)}
                        </div>
                        ${contentHtml}
                    </div>`;
                grid.addWidget(widgetHtml, node);
            });
        });
}

function saveLayout() {
    const serializedData = grid.save(true, true).children;
    const layout = serializedData.map(d => ({
        id: d.id, x: d.x, y: d.y, w: d.w, h: d.h
    }));
    
    // --- URL UPDATED HERE ---
    fetch(`${basePath}/analytics/layout/save`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(layout)
    })
    .then(response => response.json())
    .then(result => {
        if (result.status === 'success') {
            alert('Layout saved successfully!');
        } else {
            alert('Error saving layout.');
        }
    });
}

function resetLayout() {
    // This route needs to be added to the router as well
    if (confirm('Are you sure you want to reset your layout?')) {
        fetch(`${basePath}/analytics/layout/reset`, { method: 'POST' })
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    loadLayout();
                    alert('Layout has been reset.');
                } else {
                    alert('Error resetting layout.');
                }
            });
    }
}

// (The helper functions getChartTitle, getChartIcon, getChartType, etc., remain the same)
// ...

function drawChart(metric) {
    // --- URL UPDATED HERE ---
    fetch(`${basePath}/analytics/data?metric=${metric}`)
        .then(response => {
            if (!response.ok) throw new Error(`Network response was not ok for metric: ${metric}`);
            return response.json();
        })
        .then(apiData => {
            const chartDiv = document.getElementById(`${metric}_chart_div`);
            if (!chartDiv) return;
            if (apiData.error) {
                chartDiv.innerHTML = `<div class="chart-error">Error: ${apiData.error}</div>`;
                return;
            }
            // (The rest of the drawChart function remains the same)
            // ...
        })
        .catch(error => {
            const chartDiv = document.getElementById(`${metric}_chart_div`);
            if(chartDiv) chartDiv.innerHTML = `<div class="chart-error">Could not load chart.</div>`;
            console.error('Error fetching/drawing chart:', metric, error);
        });
}
// (Paste all your helper functions like getChartTitle, getChartIcon, etc., here)