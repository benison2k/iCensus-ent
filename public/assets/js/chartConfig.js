// public/assets/js/chartConfig.js

function getChartTitle(metric) {
    const t = {
        gender: 'Gender Distribution', age: 'Age Groups', purok: 'Population by Purok',
        generation_breakdown: 'Generation Breakdown', dependency_ratio: 'Dependency Ratio', sex_ratio: 'Sex Ratio',
        population_pyramid: 'Population Pyramid', average_age_of_residents: 'Average Resident Age',
        average_household_size: 'Average Household Size', civil_status: 'Civil Status', detailed_age_brackets: 'Detailed Age Brackets (10-year)',
        household_size_distribution: 'Household Size Distribution', heads_of_household_by_gender: 'Heads of Household by Gender',
        relationship: 'Relationship to Head', voter_population_by_purok: 'Voter Population by Purok',
        senior_citizens_by_purok: 'Senior Citizens by Purok', school_age_population_by_purok: 'School-Age Population by Purok',
        residents_per_street: 'Top 10 Streets by Population', nationality: 'Nationality', blood_type: 'Blood Type Distribution',
        profile_completeness: 'Profile Completeness (%)', emergency_contact_coverage: 'Emergency Contact Coverage',
        resident_status_overview: 'Resident Status Overview', civil_status_distribution_by_gender: 'Civil Status by Gender',
        educational_attainment: 'Educational Attainment', occupation: 'Top 15 Occupations', ownership_status: 'Household Ownership Status',
        pwd_distribution: 'PWD Distribution', solo_parent_distribution: 'Solo Parent Distribution', '4ps_distribution': '4Ps Beneficiaries'
    };
    return t[metric] || 'Chart';
}

function getChartIcon(metric) {
    const i = {
        gender: 'wc', age: 'cake', purok: 'location_on', generation_breakdown: 'groups',
        dependency_ratio: 'reduce_capacity', sex_ratio: 'transgender', population_pyramid: 'stacked_bar_chart',
        average_age_of_residents: 'escalator_warning', average_household_size: 'roofing', civil_status: 'favorite',
        educational_attainment: 'school', occupation: 'work', ownership_status: 'home', pwd_distribution: 'accessible',
        solo_parent_distribution: 'person', '4ps_distribution': 'savings'
    };
    return i[metric] || 'pie_chart';
}

function getChartType(metric) {
    const t = {
        average_age_of_residents: 'KPI', average_household_size: 'KPI', dependency_ratio: 'KPI',
        population_pyramid: 'PopulationPyramid', 
        civil_status_distribution_by_gender: 'GroupedBar',
        school_age_population_by_purok: 'GroupedBar',
        age: 'ColumnChart', detailed_age_brackets: 'ColumnChart', purok: 'BarChart',
        educational_attainment: 'BarChart', occupation: 'BarChart'
    };
    return t[metric] || 'PieChart';
}

function getChartExplanation(metric) {
    const explanations = {
        average_age_of_residents: 'This Key Performance Indicator (KPI) represents the average age of all residents, providing a quick snapshot of the population\'s age demographic.',
        average_household_size: 'This KPI shows the average number of residents per household. A higher number may indicate larger family sizes within the community.',
        dependency_ratio: 'This ratio compares the number of dependents (age 0-14 and 65+) to the working-age population (15-64). A higher ratio means more financial stress on the working population.',
        sex_ratio: 'This chart illustrates the proportion of male versus female residents. It helps in understanding the gender balance within the barangay.',
        population_pyramid: 'This chart shows the distribution of various age groups, separated by gender. It is crucial for understanding the age and sex structure of the population for long-term planning.',
        generation_breakdown: 'This chart categorizes the population into major generational cohorts (e.g., Gen Z, Millennials, Gen X) to show demographic distribution and potential community needs.',
        detailed_age_brackets: 'Provides a granular, 10-year breakdown of the population by age. This is useful for planning age-specific programs (e.g., for toddlers, teens, or young adults).',
        civil_status_distribution_by_gender: 'This chart breaks down the civil status (Single, Married, etc.) of residents and further separates each category by gender.',
        household_size_distribution: 'This shows how many households have 1 person, 2 people, 3 people, and so on. It helps in understanding family structures and housing needs.',
        heads_of_household_by_gender: 'This chart displays the gender distribution of individuals identified as the head of their household.',
        relationship: 'This illustrates the relationship of members to the head of the household (e.g., Spouse, Son, Daughter), giving insight into family compositions.',
        purok: 'This bar chart displays the total number of residents in each purok, helping to identify the most and least populated areas within the barangay.',
        voter_population_by_purok: 'This chart shows the number of registered voters (residents aged 18 and above) in each purok.',
        senior_citizens_by_purok: 'This chart highlights the distribution of senior citizens (residents aged 60 and above) across different puroks, useful for senior-focused programs.',
        school_age_population_by_purok: 'This visualization breaks down the population of children and teenagers by educational level (e.g., Elementary, High School) within each purok.',
        residents_per_street: 'This chart lists the top 10 most populated streets in the barangay, which can be useful for infrastructure and service planning.',
        nationality: 'Displays the breakdown of residents by nationality.',
        blood_type: 'Shows the distribution of different blood types (O, A, B, AB) among residents, which can be critical information for health emergencies.',
        profile_completeness: 'This is a data quality metric showing the percentage of resident profiles that have key information filled out, such as contact numbers or emergency contacts.',
        emergency_contact_coverage: 'This chart shows the percentage of residents who have an emergency contact person listed versus those who do not.',
        resident_status_overview: 'Provides a summary of the current status of all residents (e.g., Active, Inactive, Moved, Deceased).',
        educational_attainment: 'This chart displays the distribution of the highest educational level achieved by residents, from elementary to college graduates.',
        occupation: 'This bar chart shows the top 15 most common occupations reported by residents, providing insight into the local economy and workforce.',
        ownership_status: 'This chart breaks down the housing situation in the barangay, showing the proportion of residents who own their homes, rent, or live with relatives.',
        pwd_distribution: 'This chart shows the number of residents identified as Persons with Disabilities (PWDs) versus those who are not.',
        solo_parent_distribution: 'This chart illustrates the distribution of residents who are registered as solo parents.',
        '4ps_distribution': 'This chart shows the proportion of households that are beneficiaries of the Pantawid Pamilyang Pilipino Program (4Ps).'
    };
    return explanations[metric] || 'Detailed view of the selected metric.';
}

// Consolidate all helpers into one exportable function
export function getChartInfo(metric) {
    return {
        title: getChartTitle(metric),
        icon: getChartIcon(metric),
        type: getChartType(metric),
        explanation: getChartExplanation(metric)
    };
}