<?php
session_start();
include 'db.php';

// Disable error reporting
error_reporting(0);  
@ini_set('display_errors', 0);

// Check if the user is logged in and has a class assigned
if (!isset($_SESSION['class'])) {
    header("Location: index.php");
    exit();
}

$class = $_SESSION['class'];

// Fetch all students in the class to display in the dropdown
$studentsQuery = "SELECT first_name, last_name FROM students WHERE class = '$class'";
$studentsResult = $conn->query($studentsQuery);

// If a student is selected, get the selected student's name
$selectedStudent = '';
if (isset($_POST['student'])) {
    $selectedStudent = $_POST['student'];
}

// Fetch subjects for the class
$subjectsQuery = "SELECT * FROM subjects WHERE class = '$class'";
$subjectsResult = $conn->query($subjectsQuery);

$subjectScores = [];
$studentSummary = [];

// If a student is selected, fetch the selected student's scores
if ($selectedStudent) {
    // Fetch scores for the selected student for each subject
    while ($subject = $subjectsResult->fetch_assoc()) {
        $subjectName = $subject['subject_name'];
        
        // Fetch scores for the selected student
        $scoreQuery = "SELECT sc.class_score, sc.exam_score, sc.total
                       FROM score sc
                       WHERE sc.subject = '$subjectName' 
                       AND CONCAT(sc.first_name, ' ', sc.last_name) = '$selectedStudent'";
        $scoreResult = $conn->query($scoreQuery);
        $scoreData = $scoreResult->fetch_assoc();

        // Calculate grade based on total score
        $grade = '-';
        if ($scoreData['total'] >= 80) $grade = 1;
        elseif ($scoreData['total'] >= 70) $grade = 2;
        elseif ($scoreData['total'] >= 65) $grade = 3;
        elseif ($scoreData['total'] >= 60) $grade = 4;
        elseif ($scoreData['total'] >= 55) $grade = 5;
        elseif ($scoreData['total'] >= 50) $grade = 6;
        elseif ($scoreData['total'] >= 45) $grade = 7;
        elseif ($scoreData['total'] >= 40) $grade = 8;
        else $grade = 9;

        // Calculate rank for each subject
        $rankQuery = "SELECT CONCAT(sc.first_name, ' ', sc.last_name) AS student_name,
                             sc.subject, sc.total,
                             RANK() OVER (PARTITION BY sc.subject ORDER BY sc.total DESC) AS subject_rank
                      FROM score sc
                      WHERE sc.subject = '$subjectName'
                      ORDER BY sc.total DESC";
        $rankResult = $conn->query($rankQuery);

        $subjectRank = '-';
        while ($rankRow = $rankResult->fetch_assoc()) {
            if ($rankRow['student_name'] === $selectedStudent) {
                $subjectRank = $rankRow['subject_rank'];
                break;
            }
        }

        // Store the subject scores for the selected student, including rank
        $subjectScores[] = [
            'subject' => $subjectName,
            'class_score' => $scoreData['class_score'] ?? 0,
            'exam_score' => $scoreData['exam_score'] ?? 0,
            'total' => $scoreData['total'] ?? 0,
            'grade' => $grade,
            'rank' => $subjectRank
        ];
    }

    // Fetch summary for the student (total scores and final grade)
    $summaryQuery = "SELECT SUM(sc.class_score) AS total_class_score, SUM(sc.exam_score) AS total_exam_score, SUM(sc.total) AS total
                     FROM score sc
                     WHERE CONCAT(sc.first_name, ' ', sc.last_name) = '$selectedStudent'";
    $summaryResult = $conn->query($summaryQuery);
    $summaryData = $summaryResult->fetch_assoc();

    // Calculate overall rank based on total scores
    $rankQuery = "SELECT CONCAT(sc.first_name, ' ', sc.last_name) AS student_name,
                         SUM(sc.total) AS student_total,
                         RANK() OVER (ORDER BY SUM(sc.total) DESC) AS student_rank
                  FROM score sc
                  WHERE sc.class = '$class'
                  GROUP BY student_name";
    $rankResult = $conn->query($rankQuery);

    // Find the selected student's rank
    $overallRank = '-';
    while ($rankRow = $rankResult->fetch_assoc()) {
        if ($rankRow['student_name'] === $selectedStudent) {
            $overallRank = $rankRow['student_rank'];
            break;
        }
    }

    $studentSummary = [
        'total_class_score' => $summaryData['total_class_score'],
        'total_exam_score' => $summaryData['total_exam_score'],
        'overall_total' => $summaryData['total'],
        'final_grade' => $summaryData['total'] >= 80 ? 1 : ($summaryData['total'] >= 70 ? 2 : 3),
        'overall_rank' => $overallRank
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .print-btn { margin-top: 20px; }
        .report-table, .report-table th, .report-table td { border: 1px solid black; border-collapse: collapse; padding: 8px; text-align: center; }
        .printable-section { width: 100%; }
        @media print { 
            body * { visibility: hidden; }
            .printable-section, .printable-section * { visibility: visible; }
            .printable-section { width: 100%; page-break-before: always; }
        .footer { margin-top: auto; padding: 10px 0; background-color: #343a40; color: white; text-align: center; }
    
        }
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
        <h1>Student Report</h1>

        <!-- Student Selection Form -->
        <form method="post">
            <div class="mb-3">
                <label for="student" class="form-label">Select Student</label>
                <select name="student" id="student" class="form-select" onchange="this.form.submit()">
                    <option value="">Select a Student</option>
                    <?php while ($student = $studentsResult->fetch_assoc()): ?>
                        <option value="<?php echo $student['first_name'] . ' ' . $student['last_name']; ?>"
                            <?php echo $selectedStudent == $student['first_name'] . ' ' . $student['last_name'] ? 'selected' : ''; ?>>
                            <?php echo $student['first_name'] . ' ' . $student['last_name']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </form>

        <?php if ($selectedStudent): ?>
            <!-- Student Name and Table for Subject Scores -->
            <h2><?php echo $selectedStudent; ?>'s Report</h2>
            <table class="table report-table">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Class Score</th>
                        <th>Exam Score</th>
                        <th>Total</th>
                        <th>Grade</th>
                        <th>Rank</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subjectScores as $subjectScore): ?>
                        <tr>
                            <td><?php echo $subjectScore['subject']; ?></td>
                            <td><?php echo $subjectScore['class_score']; ?></td>
                            <td><?php echo $subjectScore['exam_score']; ?></td>
                            <td><?php echo $subjectScore['total']; ?></td>
                            <td><?php echo $subjectScore['grade']; ?></td>
                            <td><?php echo $subjectScore['rank']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Overall Summary -->
            <h3>Overall Summary</h3>
            <table class="table report-table">
                <thead>
                    <tr>
                        <th>Total Class Score</th>
                        <th>Total Exam Score</th>
                        <th>Overall Total</th>
                        <th>Final Grade</th>
                        <th>Overall Rank</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo $studentSummary['total_class_score']; ?></td>
                        <td><?php echo $studentSummary['total_exam_score']; ?></td>
                        <td><?php echo $studentSummary['overall_total']; ?></td>
                        <td><?php echo $studentSummary['final_grade']; ?></td>
                        <td><?php echo $studentSummary['overall_rank']; ?></td>
                    </tr>
                </tbody>
            </table>

            <!-- Print Button -->
            <button class="btn btn-primary print-btn" onclick="window.print()">Print</button>
        <?php endif; ?>
    </div>
        <!-- Footer -->
        <footer class="footer">
        <div class="container">
            <span>Copyright © Opera Media Solutions</span>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
