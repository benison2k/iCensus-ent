<?php
session_start();
header('Content-Type: application/json');
$config = require __DIR__ . '/config.php';
require __DIR__ . '/Database.php';

if (!isset($_SESSION['user']['id'])) {
    echo json_encode([]);
    exit;
}

$db = new Database($config);
$pdo = $db->getPdo();

$stmt = $pdo->prepare("SELECT layout FROM user_analytics_layouts WHERE user_id = ?");
$stmt->execute([$_SESSION['user']['id']]);
$result = $stmt->fetchColumn();
$layout = json_decode($result);

// If there is no saved layout OR the saved layout is empty, use the default.
if (!$result || empty($layout)) {
    $defaultLayout = json_encode([
        // --- ROW 1: Key KPIs (h:2 = 160px tall) ---
        ['id' => 'average_age_of_residents', 'x' => 0, 'y' => 0, 'w' => 3, 'h' => 2],
        ['id' => 'average_household_size', 'x' => 3, 'y' => 0, 'w' => 3, 'h' => 2],
        ['id' => 'dependency_ratio', 'x' => 6, 'y' => 0, 'w' => 3, 'h' => 2],
        ['id' => 'sex_ratio', 'x' => 9, 'y' => 0, 'w' => 3, 'h' => 2],

        // --- ROW 2: Core Demographics (y:2 starts after KPI row) ---
        ['id' => 'population_pyramid', 'x' => 0, 'y' => 2, 'w' => 6, 'h' => 6],
        ['id' => 'generation_breakdown', 'x' => 6, 'y' => 2, 'w' => 6, 'h' => 6],

        // --- ROW 3: Deeper Demographics ---
        ['id' => 'detailed_age_brackets', 'x' => 0, 'y' => 8, 'w' => 6, 'h' => 4],
        ['id' => 'civil_status_distribution_by_gender', 'x' => 6, 'y' => 8, 'w' => 6, 'h' => 4],

        // --- ROW 4: Household & Social Structure ---
        ['id' => 'household_size_distribution', 'x' => 0, 'y' => 12, 'w' => 4, 'h' => 4],
        ['id' => 'heads_of_household_by_gender', 'x' => 4, 'y' => 12, 'w' => 4, 'h' => 4],
        ['id' => 'relationship', 'x' => 8, 'y' => 12, 'w' => 4, 'h' => 4],

        // --- ROW 5: Location-Based Analysis (Puroks) ---
        ['id' => 'purok', 'x' => 0, 'y' => 16, 'w' => 6, 'h' => 4],
        ['id' => 'voter_population_by_purok', 'x' => 6, 'y' => 16, 'w' => 6, 'h' => 4],
        
        // --- ROW 6: More Purok Analysis ---
        ['id' => 'senior_citizens_by_purok', 'x' => 0, 'y' => 20, 'w' => 6, 'h' => 4],
        ['id' => 'school_age_population_by_purok', 'x' => 6, 'y' => 20, 'w' => 6, 'h' => 4],
        
        // --- ROW 7: Location & Misc ---
        ['id' => 'residents_per_street', 'x' => 0, 'y' => 24, 'w' => 6, 'h' => 4],
        ['id' => 'nationality', 'x' => 6, 'y' => 24, 'w' => 3, 'h' => 4],
        ['id' => 'blood_type', 'x' => 9, 'y' => 24, 'w' => 3, 'h' => 4],

        // --- ROW 8: Data Health & Status ---
        ['id' => 'profile_completeness', 'x' => 0, 'y' => 28, 'w' => 4, 'h' => 4],
        ['id' => 'emergency_contact_coverage', 'x' => 4, 'y' => 28, 'w' => 4, 'h' => 4],
        ['id' => 'resident_status_overview', 'x' => 8, 'y' => 28, 'w' => 4, 'h' => 4],
    ]);
    echo $defaultLayout;
} else {
    echo $result;
}