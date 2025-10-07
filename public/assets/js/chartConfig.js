// public/assets/js/chartConfig.js

export const chartConfig = {
    // KPI Metrics
    average_age_of_residents: {
        title: 'Average Resident Age',
        icon: 'escalator_warning',
        type: 'KPI',
        explanation: 'This KPI represents the average age of all residents, providing a quick snapshot of the population\'s age demographic.'
    },
    average_household_size: {
        title: 'Average Household Size',
        icon: 'roofing',
        type: 'KPI',
        explanation: 'This KPI shows the average number of residents per household. A higher number may indicate larger family sizes.'
    },
    dependency_ratio: {
        title: 'Dependency Ratio',
        icon: 'reduce_capacity',
        type: 'KPI',
        explanation: 'This ratio compares dependents (age 0-14 & 65+) to the working-age population (15-64). A higher ratio means more financial stress on the working population.'
    },

    // Pie & Donut Charts
    gender: { title: 'Gender Distribution', icon: 'wc', type: 'PieChart', explanation: 'A breakdown of residents by gender.' },
    civil_status: { title: 'Civil Status', icon: 'favorite', type: 'PieChart', explanation: 'Shows the distribution of civil statuses like Single, Married, etc.' },
    sex_ratio: { title: 'Sex Ratio', icon: 'transgender', type: 'PieChart', explanation: 'Illustrates the proportion of male versus female residents.' },
    blood_type: { title: 'Blood Type Distribution', icon: 'bloodtype', type: 'PieChart', explanation: 'Shows the distribution of different blood types.' },
    nationality: { title: 'Nationality', icon: 'flag', type: 'PieChart', explanation: 'Displays the breakdown of residents by nationality.' },
    emergency_contact_coverage: { title: 'Emergency Contact Coverage', icon: 'contact_phone', type: 'PieChart', explanation: 'Percentage of residents with an emergency contact listed.' },
    resident_status_overview: { title: 'Resident Status Overview', icon: 'assignment_ind', type: 'PieChart', explanation: 'A summary of the current status of all residents (e.g., Active, Inactive, Moved).'},
    ownership_status: { title: 'Household Ownership Status', icon: 'home', type: 'PieChart', explanation: 'Breaks down the housing situation (e.g., Owned, Rented).'},
    pwd_distribution: { title: 'PWD Distribution', icon: 'accessible', type: 'PieChart', explanation: 'Shows the number of residents identified as Persons with Disabilities.'},
    solo_parent_distribution: { title: 'Solo Parent Distribution', icon: 'person', type: 'PieChart', explanation: 'Illustrates the distribution of residents who are registered as solo parents.'},
    '4ps_distribution': { title: '4Ps Beneficiaries', icon: 'savings', type: 'PieChart', explanation: 'Shows the proportion of households that are beneficiaries of the 4Ps program.'},
    heads_of_household_by_gender: { title: 'Heads of Household by Gender', icon: 'person_pin', type: 'PieChart', explanation: 'Displays the gender distribution of individuals identified as the head of their household.'},
    relationship: { title: 'Relationship to Head', icon: 'family_restroom', type: 'PieChart', explanation: 'Illustrates the relationship of members to the head of the household.'},
    household_size_distribution: { title: 'Household Size Distribution', icon: 'groups', type: 'PieChart', explanation: 'Shows how many households have 1 person, 2 people, 3 people, and so on.'},

    // Column Charts
    age: { title: 'Age Groups', icon: 'cake', type: 'ColumnChart', explanation: 'A distribution of residents across broad age groups.' },
    detailed_age_brackets: { title: 'Detailed Age Brackets (10-year)', icon: 'bar_chart', type: 'ColumnChart', explanation: 'A granular, 10-year breakdown of the population by age.' },

    // Bar Charts
    purok: { title: 'Population by Purok', icon: 'location_on', type: 'BarChart', explanation: 'Displays the total number of residents in each purok.' },
    educational_attainment: { title: 'Educational Attainment', icon: 'school', type: 'BarChart', explanation: 'Displays the distribution of the highest educational level achieved by residents.'},
    occupation: { title: 'Top 15 Occupations', icon: 'work', type: 'BarChart', explanation: 'Shows the top 15 most common occupations reported by residents.'},
    residents_per_street: { title: 'Top 10 Streets by Population', icon: 'signpost', type: 'BarChart', explanation: 'Lists the top 10 most populated streets in the barangay.'},

    // Grouped/Special Charts
    population_pyramid: {
        title: 'Population Pyramid',
        icon: 'stacked_bar_chart',
        type: 'PopulationPyramid',
        explanation: 'Shows the distribution of various age groups, separated by gender, which is crucial for long-term planning.'
    },
    civil_status_distribution_by_gender: {
        title: 'Civil Status by Gender',
        icon: 'wc',
        type: 'GroupedBar',
        explanation: 'This chart breaks down the civil status of residents and further separates each category by gender.'
    },
    school_age_population_by_purok: {
        title: 'School-Age Population by Purok',
        icon: 'school',
        type: 'GroupedBar',
        explanation: 'This visualization breaks down the population of children and teenagers by educational level within each purok.'
    },
    
    // Fallback
    default: {
        title: 'Chart',
        icon: 'pie_chart',
        type: 'PieChart',
        explanation: 'Detailed view of the selected metric.'
    }
};