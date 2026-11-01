<?php
declare(strict_types=1);

// Reference tables are the single source for account form options.
$allowedCourses = [];
foreach ($conn->query('SELECT course_code, course_name FROM courses WHERE is_active = 1 ORDER BY course_code')->fetch_all(MYSQLI_ASSOC) as $course) {
    $allowedCourses[$course['course_code']] = $course['course_name'];
}
$allowedYearLevels = array_column(
    $conn->query('SELECT year_name FROM year_levels WHERE is_active = 1 ORDER BY sort_order, year_name')->fetch_all(MYSQLI_ASSOC),
    'year_name'
);
