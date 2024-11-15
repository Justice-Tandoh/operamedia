<?php
session_start();
include 'db.php';

if (!isset($_SESSION['class'])) {
    header("Location: index.php");
    exit();
}

$class = $_SESSION['class'];

// Count students and subjects for the user's class
$studentCountQuery = "SELECT COUNT(*) AS total_students FROM students WHERE class = '$class'";
$studentCountResult = $conn->query($studentCountQuery);
$studentCount = $studentCountResult->fetch_assoc()['total_students'];

$subjectCountQuery = "SELECT COUNT(*) AS total_subjects FROM subjects WHERE class = '$class'";
$subjectCountResult = $conn->query($subjectCountQuery);
$subjectCount = $subjectCountResult->fetch_assoc()['total_subjects'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { display: flex; flex-direction: column; min-height: 100vh; background-color: #f8f9fa; }
        .container { margin-top: 20px; }
        .card { background-color: Gold; color: Black; text-align: center; margin-bottom: 20px; }
        .footer { margin-top: auto; padding: 10px 0; background-color: #343a40; color: white; text-align: center; }
        .btn { margin: 5px; }
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

    <!-- Main Content -->
    <div class="container">
        <div class="row">
        <h1>Welcome, <?php echo $_SESSION['username']; ?></h1>
            <p>Class: <?php echo $class; ?></p>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5>Total Students</h5>
                        <p class="display-4"><?php echo $studentCount; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5>Total Subjects</h5>
                        <p class="display-4"><?php echo $subjectCount; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Buttons -->
        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
            <a href="add_student.php" class="btn btn-primary">Register Student</a>
            <a href="add_subject.php" class="btn btn-primary">Add Subject</a>
            <a href="add_scores.php" class="btn btn-primary">Add Scores</a>
            <a href="view_mastersheet.php" class="btn btn-primary">View Master Sheet</a>
            <a href="report.php" class="btn btn-primary">View Report Sheet</a>
        </div>
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
