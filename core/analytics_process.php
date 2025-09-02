<?php
function fetchAnalyticsData($pdo) {
    $stmt = $pdo->query("SELECT dob, gender, status, purok FROM residents");
    $residents_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Aggregate initial data for the default chart
    $status_counts = array_count_values(array_filter(array_column($residents_data, 'status')));

    return [
        'residents' => $residents_data, // Pass the full dataset to the client
        'status' => $status_counts // Pre-aggregated data for the default chart
    ];
}
?>