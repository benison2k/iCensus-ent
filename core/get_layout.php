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

if (!$result || empty(json_decode($result))) {
    $defaultLayout = json_encode([
        // --- ROW 1: Key KPIs ---
        ['id' => 'average_age_of_residents', 'x' => 0, 'y' => 0, 'w' => 3, 'h' => 1],
        ['id' => 'average_household_size', 'x' => 3, 'y' => 0, 'w' => 3, 'h' => 1],
        ['id' => 'dependency_ratio', 'x' => 6, 'y' => 0, 'w' => 3, 'h' => 1],
        ['id' => 'sex_ratio', 'x' => 9, 'y' => 0, 'w' => 3, 'h' => 1],

        // --- ROW 2: Core Demographics ---
        ['id' => 'population_pyramid', 'x' => 0, 'y' => 1, 'w' => 6, 'h' => 3],
        ['id' => 'generation_breakdown', 'x' => 6, 'y' => 1, 'w' => 6, 'h' => 3],

        // --- ROW 3: Deeper Demographics ---
        ['id' => 'detailed_age_brackets', 'x' => 0, 'y' => 4, 'w' => 6, 'h' => 2],
        ['id' => 'civil_status_distribution_by_gender', 'x' => 6, 'y' => 4, 'w' => 6, 'h' => 2],

        // --- ROW 4: Household & Social Structure ---
        ['id' => 'household_size_distribution', 'x' => 0, 'y' => 6, 'w' => 4, 'h' => 2],
        ['id' => 'heads_of_household_by_gender', 'x' => 4, 'y' => 6, 'w' => 4, 'h' => 2],
        ['id' => 'relationship', 'x' => 8, 'y' => 6, 'w' => 4, 'h' => 2],

        // --- ROW 5: Location-Based Analysis (Puroks) ---
        ['id' => 'purok', 'x' => 0, 'y' => 8, 'w' => 6, 'h' => 2],
        ['id' => 'voter_population_by_purok', 'x' => 6, 'y' => 8, 'w' => 6, 'h' => 2],
        
        // --- ROW 6: More Purok Analysis ---
        ['id' => 'senior_citizens_by_purok', 'x' => 0, 'y' => 10, 'w' => 6, 'h' => 2],
        ['id' => 'school_age_population_by_purok', 'x' => 6, 'y' => 10, 'w' => 6, 'h' => 2],
        
        // --- ROW 7: Location & Misc ---
        ['id' => 'residents_per_street', 'x' => 0, 'y' => 12, 'w' => 6, 'h' => 2],
        ['id' => 'nationality', 'x' => 6, 'y' => 12, 'w' => 3, 'h' => 2],
        ['id' => 'blood_type', 'x' => 9, 'y' => 12, 'w' => 3, 'h' => 2],

        // --- ROW 8: Data Health & Status ---
        ['id' => 'profile_completeness', 'x' => 0, 'y' => 14, 'w' => 4, 'h' => 2],
        ['id' => 'emergency_contact_coverage', 'x' => 4, 'y' => 14, 'w' => 4, 'h' => 2],
        ['id' => 'resident_status_overview', 'x' => 8, 'y' => 14, 'w' => 4, 'h' => 2],
    ]);
    echo $defaultLayout;
} else {
    echo $result;
}