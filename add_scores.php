<?php
session_start();
include 'db.php';

// Check if the user is logged in and has a class assigned
if (!isset($_SESSION['class'])) {
    header("Location: index.php");
    exit();
}

$class = $_SESSION['class'];

// Fetch students in the user's class
$studentsQuery = "SELECT * FROM students WHERE class = '$class'";
$studentsResult = $conn->query($studentsQuery);

// Fetch subjects for the user's class from the 'subjects' table
$subjectsQuery = "SELECT * FROM subjects WHERE class = '$class'";
$subjectsResult = $conn->query($subjectsQuery);

// Process form submission to add or update scores
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_first_name = $_POST['student_first_name'];
    $student_last_name = $_POST['student_last_name'];
    $subject = $_POST['subject'];
    $class_score = $_POST['class_score'];
    $exam_score = $_POST['exam_score'];
    $total = $class_score + $exam_score;

    // Grade Calculation based on GES system
    $grade = '';
    if ($total >= 80) {
        $grade = '1';
        $remarks = 'Excellent';
    } elseif ($total >= 70) {
        $grade = '2';
        $remarks = 'Very Good';
    } elseif ($total >= 60) {
        $grade = '3';
        $remarks = 'Good';
    } elseif ($total >= 55) {
        $grade = '4';
        $remarks = 'Credit';
    } elseif ($total >= 40) {
        $grade = '5';
        $remarks = 'Credit';
    } elseif ($total >= 35) {
        $grade = '6';
        $remarks = 'Credit';
    } elseif ($total >= 30) {
        $grade = '7';
        $remarks = 'Pass';
    } elseif ($total >= 40) {
        $grade = '8';
        $remarks = 'Pass';
    } else {
        $grade = '9';
        $remarks = 'Fail';
    }

    // Retrieve the 'is_core' value for the selected subject
    $subjectQuery = "SELECT is_core FROM subjects WHERE subject_name = '$subject' AND class = '$class'";
    $subjectResult = $conn->query($subjectQuery);
    $subjectData = $subjectResult->fetch_assoc();
    $is_core = $subjectData['is_core'];

    // Check if the student already has a score for the selected subject
    $checkQuery = "SELECT * FROM score WHERE first_name = '$student_first_name' AND last_name = '$student_last_name' AND subject = '$subject'";
    $checkResult = $conn->query($checkQuery);

    if ($checkResult->num_rows > 0) {
        // If record exists, update the existing score
        $updateQuery = "UPDATE score SET class_score = '$class_score', exam_score = '$exam_score', total = '$total', grade = '$grade', remarks = '$remarks', is_core = '$is_core' 
                        WHERE first_name = '$student_first_name' AND last_name = '$student_last_name' AND subject = '$subject'";

        if ($conn->query($updateQuery) === TRUE) {
            echo "Score updated successfully.";
        } else {
            echo "Error: " . $conn->error;
        }
    } else {
        // If no record exists, insert the new score
        $insertQuery = "INSERT INTO score (first_name, last_name, class, subject, class_score, exam_score, total, grade, remarks, is_core)
                        VALUES ('$student_first_name', '$student_last_name', '$class', '$subject', '$class_score', '$exam_score', '$total', '$grade', '$remarks', '$is_core')";

        if ($conn->query($insertQuery) === TRUE) {
            echo "Score added successfully.";
        } else {
            echo "Error: " . $conn->error;
        }
    }
}

// Fetch subjects for the 'View Scores' section (from subjects table)
$subjectsQueryForView = "SELECT * FROM subjects WHERE class = '$class'";
$subjectsResultForView = $conn->query($subjectsQueryForView);

// Handle View Scores
$scores = [];
if (isset($_GET['view_subject']) && !empty($_GET['view_subject'])) {
    $view_subject = $_GET['view_subject'];

    // Fetch the scores for the selected subject and class, ordered by total score descending
    $viewScoresQuery = "SELECT s.first_name, s.last_name, sc.class_score, sc.exam_score, sc.total, sc.grade, sc.remarks, sc.subject
                        FROM score sc
                        JOIN students s ON s.first_name = sc.first_name AND s.last_name = sc.last_name
                        WHERE sc.subject = '$view_subject' AND sc.class = '$class'
                        ORDER BY sc.total DESC";
    $scoresResult = $conn->query($viewScoresQuery);

    if ($scoresResult->num_rows > 0) {
        $rank = 1; // Ranking variable
        while ($score = $scoresResult->fetch_assoc()) {
            $scores[] = [
                'first_name' => $score['first_name'],
                'last_name' => $score['last_name'],
                'class_score' => $score['class_score'],
                'exam_score' => $score['exam_score'],
                'total' => $score['total'],
                'grade' => $score['grade'],
                'remarks' => $score['remarks'],
                'subject_name' => $score['subject'], // Showing the subject name
                'rank' => $rank++
            ];
        }
    } else {
        $scores[] = 'No scores available for this subject.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View and Add Scores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .print-btn {
            margin-top: 20px;
        }
        .navbar {
            margin-bottom: 30px;
        }
        .footer { margin-top: auto; padding: 10px 0; background-color: #343a40; color: white; text-align: center; }

        /* Print styling */
        @media print {
            body * {
                visibility: hidden;
            }
            .printable-table, .printable-table * {
                visibility: visible;
            }
            .printable-table {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
            }
            .navbar, .footer, .print-btn {
                display: none;
            }
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

<div class="container mt-5">
    <h1>Manage Scores</h1>
    
    <!-- Form to Add/Update Scores -->
    <form method="POST" action="add_scores.php">
        <div class="mb-3">
            <label for="student_first_name" class="form-label">Select Student</label>
            <select name="student_first_name" id="student_first_name" class="form-control" required>
                <option value="">Choose Student</option>
                <?php while ($student = $studentsResult->fetch_assoc()): ?>
                    <option value="<?php echo $student['first_name']; ?>" data-last-name="<?php echo $student['last_name']; ?>">
                        <?php echo $student['first_name'] . " " . $student['last_name']; ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="subject" class="form-label">Subject</label>
            <select name="subject" id="subject" class="form-control" required>
                <option value="">Choose Subject</option>
                <?php while ($subject = $subjectsResult->fetch_assoc()): ?>
                    <option value="<?php echo $subject['subject_name']; ?>">
                        <?php echo $subject['subject_name']; ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="class_score" class="form-label">Class Score</label>
            <input type="number" name="class_score" id="class_score" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="exam_score" class="form-label">Exam Score</label>
            <input type="number" name="exam_score" id="exam_score" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Add/Update Score</button>
    </form>

    <!-- View Scores Section -->
    <h2 class="mt-5">View Scores</h2>
    <form method="GET" action="add_scores.php">
        <label for="view_subject" class="form-label">Select Subject to View Scores</label>
        <select name="view_subject" id="view_subject" class="form-control">
            <option value="">Choose Subject</option>
            <?php while ($subject = $subjectsResultForView->fetch_assoc()): ?>
                <option value="<?php echo $subject['subject_name']; ?>">
                    <?php echo $subject['subject_name']; ?>
                </option>
            <?php endwhile; ?>
        </select>
        <button type="submit" class="btn btn-success mt-2">View Scores</button>
    </form>

    <!-- Display Scores in a Table -->
    <?php if (!empty($scores)): ?>
        <div class="table-responsive mt-4 printable-table">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Class Score</th>
                        <th>Exam Score</th>
                        <th>Total</th>
                        <th>Grade</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($scores as $score): ?>
                        <tr>
                            <td><?php echo $score['rank']; ?></td>
                            <td><?php echo $score['first_name']; ?></td>
                            <td><?php echo $score['last_name']; ?></td>
                            <td><?php echo $score['class_score']; ?></td>
                            <td><?php echo $score['exam_score']; ?></td>
                            <td><?php echo $score['total']; ?></td>
                            <td><?php echo $score['grade']; ?></td>
                            <td><?php echo $score['remarks']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button class="btn btn-secondary print-btn" onclick="window.print();">Print Scores</button>
        </div>
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

<?php $conn->close(); ?>
