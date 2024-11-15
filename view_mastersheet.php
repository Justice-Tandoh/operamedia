<?php
session_start();
include 'db.php';

// Check if the user is logged in and has a class assigned
if (!isset($_SESSION['class'])) {
    header("Location: index.php");
    exit();
}

$class = $_SESSION['class'];

// Fetch subjects for the class
$subjectsQuery = "SELECT * FROM subjects WHERE class = '$class'";
$subjectsResult = $conn->query($subjectsQuery);

$subjectScores = [];
$studentTotals = [];

// Loop through each subject and fetch student scores
while ($subject = $subjectsResult->fetch_assoc()) {
    $subjectName = $subject['subject_name'];
    $subjectType = $subject['is_core']; // 'is_core' determines if it's core or elective
    
    // Fetch all students and their scores for this subject
    $studentsQuery = "SELECT s.first_name, s.last_name, sc.class_score, sc.exam_score, sc.total
                      FROM students s
                      LEFT JOIN score sc ON s.first_name = sc.first_name AND s.last_name = sc.last_name AND sc.subject = '$subjectName'
                      WHERE s.class = '$class'";
    $studentsResult = $conn->query($studentsQuery);

    while ($studentScore = $studentsResult->fetch_assoc()) {
        $studentName = $studentScore['first_name'] . " " . $studentScore['last_name'];
        $classScore = $studentScore['class_score'] ?? 0;
        $examScore = $studentScore['exam_score'] ?? 0;
        $total = $studentScore['total'] ?? 0;

        // Calculate WAEC BECE grade
        $grade = '-';
        if ($total >= 80) $grade = 1;
        elseif ($total >= 70) $grade = 2;
        elseif ($total >= 65) $grade = 3;
        elseif ($total >= 60) $grade = 4;
        elseif ($total >= 55) $grade = 5;
        elseif ($total >= 50) $grade = 6;
        elseif ($total >= 45) $grade = 7;
        elseif ($total >= 40) $grade = 8;
        else $grade = 9;

        // Initialize student data if not already done
        if (!isset($studentTotals[$studentName])) {
            $studentTotals[$studentName] = [
                'class_score_total' => 0,
                'exam_score_total' => 0,
                'overall_total' => 0,
                'core_subjects' => [],
                'elective_subjects' => []
            ];
        }

        // Update student totals
        $studentTotals[$studentName]['class_score_total'] += $classScore;
        $studentTotals[$studentName]['exam_score_total'] += $examScore;
        $studentTotals[$studentName]['overall_total'] += $total;

        // Add scores to core or elective arrays
        if ($subjectType === 'core') {
            $studentTotals[$studentName]['core_subjects'][] = $total;
        } else {
            $studentTotals[$studentName]['elective_subjects'][] = $total;
        }

        // Collect scores for each subject
        $subjectScores[$subjectName][] = [
            'name' => $studentName,
            'class_score' => $classScore,
            'exam_score' => $examScore,
            'total' => $total,
            'grade' => $grade
        ];
    }
}

// Sort student names in ascending order for each subject
foreach ($subjectScores as &$students) {
    usort($students, fn($a, $b) => strcmp($a['name'], $b['name']));
}

// Calculate best core and elective totals for each student
foreach ($studentTotals as $name => &$totals) {
    rsort($totals['core_subjects']);
    rsort($totals['elective_subjects']);
    $totals['best_core_total'] = array_sum(array_slice($totals['core_subjects'], 0, 3));
    $totals['best_elective_total'] = array_sum(array_slice($totals['elective_subjects'], 0, 3));
    $totals['final_grade'] = $totals['best_core_total'] + $totals['best_elective_total'];
}

// Sort students by overall total for ranking
uasort($studentTotals, fn($a, $b) => $b['overall_total'] <=> $a['overall_total']);

// Assign ranks
$rank = 1;
foreach ($studentTotals as &$totals) {
    $totals['rank'] = $rank++;
}
unset($totals);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Master Sheet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .footer { margin-top: auto; padding: 10px 0; background-color: #343a40; color: white; text-align: center; }
        .print-btn { margin-top: 20px; }
        .report-table, .report-table th, .report-table td { border: 1px solid black; border-collapse: collapse; padding: 8px; text-align: center; }
        .printable-section { width: 100%; }        
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Opera Media Solutions</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link active" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="add_student.php">Register Student</a></li>
                    <li class="nav-item"><a class="nav-link" href="add_subject.php">Add Subject</a></li>
                    <li class="nav-item"><a class="nav-link" href="add_scores.php">Add Scores</a></li>
                    <li class="nav-item"><a class="nav-link" href="view_mastersheet.php">View Master Sheet</a></li>
                    <li class="nav-item"><a class="nav-link" href="report.php">View Report Sheet</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-5 printable-section">
        <h1>Class Master Sheet - Grouped by Subject</h1>
        
        <?php foreach ($subjectScores as $subjectName => $students): ?>
            <h2>Subject: <?php echo $subjectName; ?></h2>
            <table class="table report-table">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Class Score</th>
                        <th>Exam Score</th>
                        <th>Total</th>
                        <th>Grade</th>
                        <th>Rank</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $index => $student): ?>
                        <tr>
                            <td><?php echo $student['name']; ?></td>
                            <td><?php echo $student['class_score']; ?></td>
                            <td><?php echo $student['exam_score']; ?></td>
                            <td><?php echo $student['total']; ?></td>
                            <td><?php echo $student['grade']; ?></td>
                            <td><?php echo $index + 1; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endforeach; ?>

        <h2>Overall Summary</h2>
        <table class="table report-table">
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Total Class Score</th>
                    <th>Total Exam Score</th>
                    <th>Overall Total</th>
                    <th>Best Core Total</th>
                    <th>Best Elective Total</th>
                    <th>Final Grade Total</th>
                    <th>Rank</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($studentTotals as $name => $totals): ?>
                    <tr>
                        <td><?php echo $name; ?></td>
                        <td><?php echo $totals['class_score_total']; ?></td>
                        <td><?php echo $totals['exam_score_total']; ?></td>
                        <td><?php echo $totals['overall_total']; ?></td>
                        <td><?php echo $totals['best_core_total']; ?></td>
                        <td><?php echo $totals['best_elective_total']; ?></td>
                        <td><?php echo $totals['final_grade']; ?></td>
                        <td><?php echo $totals['rank']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <button class="btn btn-primary print-btn" onclick="window.print();">Print Report</button>
    </div>
    
    <div class="footer">
        <p>&copy; 2024 Opera Media Solutions</p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
