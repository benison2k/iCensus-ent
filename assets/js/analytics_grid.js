document.addEventListener('DOMContentLoaded', function () {
    // Wait for the Google Charts to be ready before initializing the grid
    google.charts.setOnLoadCallback(initializeGrid);

    function initializeGrid() {
        const options = {
            column: 4,
            cellHeight: 'auto',
            margin: 10,
            disableOneColumnMode: true,
            // Make widgets resizable and draggable
            resizable: { handles: 'e, se, s, sw, w' },
        };
        const grid = GridStack.init(options);

        const saveBtn = document.getElementById('save-layout-btn');

        // Function to save the layout
        function saveLayout() {
            const serializedData = grid.save();
            
            saveBtn.textContent = 'Saving...';
            saveBtn.disabled = true;

            fetch('../core/analytics_layout_process.php?action=save', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(serializedData),
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    saveBtn.textContent = 'Layout Saved!';
                } else {
                    throw new Error(data.message || 'Failed to save');
                }
            })
            .catch(error => {
                console.error('Error saving layout:', error);
                saveBtn.textContent = 'Error!';
            })
            .finally(() => {
                setTimeout(() => {
                    saveBtn.textContent = 'Save Layout';
                    saveBtn.disabled = false;
                }, 2000);
            });
        }

        // Function to load the layout
        function loadLayout() {
            fetch('../core/analytics_layout_process.php?action=load')
            .then(res => res.json())
            .then(items => {
                // Ensure there are items to load before calling load
                if (items && items.length > 0) {
                    grid.load(items);
                }
            })
            .catch(error => {
                console.error('Error loading layout:', error);
            });
        }

        saveBtn.addEventListener('click', saveLayout);
        
        // Load layout after initializing
        loadLayout();

        // Redraw charts on resize stop to prevent them from getting cut off
        grid.on('resizestop', function(event, el) {
            const chartId = el.querySelector('.chart-container').id;
            // This is a simplified redraw, you might need a more robust way
            // to call the specific chart's draw function if this doesn't work.
            window.drawAllCharts(); 
        });
    }
});