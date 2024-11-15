<?php
session_start();
include 'db.php';

// Check if the user is logged in and has a class assigned
if (!isset($_SESSION['class'])) {
    header("Location: index.php");
    exit();
}

$class = $_SESSION['class'];

// Process form submission to add or update a subject
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_subject'])) {
    $subject_name = $_POST['subject_name'];
    $subject_type = $_POST['subject_type']; // "Core" or "Elective"

    // Check if the subject name contains @! for updating an existing subject
    if (strpos($subject_name, '@!') !== false) {
        // Split the subject name into existing and new subject names
        list($existing_subject, $new_subject) = explode('@!', $subject_name, 2);

        // Update the existing subject in the database
        $updateQuery = "UPDATE subjects SET subject_name = '$new_subject' WHERE subject_name = '$existing_subject' AND class = '$class'";

        if ($conn->query($updateQuery) === TRUE) {
            echo "<div class='alert alert-success'>Subject updated successfully.</div>";
        } else {
            echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
        }
    } else {
        // Check the number of core subjects if the selected type is core
        if ($subject_type === 'core') {
            $coreCountQuery = "SELECT COUNT(*) as core_count FROM subjects WHERE is_core = 'Core' AND class = '$class'";
            $coreCountResult = $conn->query($coreCountQuery);
            $coreCount = $coreCountResult->fetch_assoc()['core_count'];

            if ($coreCount >= 4) {
                echo "<div class='alert alert-danger'>You can only create a maximum of 4 core subjects.</div>";
            } else {
                // Insert core subject if limit not exceeded
                $insertQuery = "INSERT INTO subjects (subject_name, class, is_core) VALUES ('$subject_name', '$class', 'Core')";
                if ($conn->query($insertQuery) === TRUE) {
                    echo "<div class='alert alert-success'>Core subject added successfully.</div>";
                } else {
                    echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
                }
            }
        } else {
            // Insert elective subject directly
            $insertQuery = "INSERT INTO subjects (subject_name, class, is_core) VALUES ('$subject_name', '$class', 'Elective')";
            if ($conn->query($insertQuery) === TRUE) {
                echo "<div class='alert alert-success'>Elective subject added successfully.</div>";
            } else {
                echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
            }
        }
    }
}

// Fetch the list of subjects for the user's class
$subjectsQuery = "SELECT * FROM subjects WHERE class = '$class'";
$subjectsResult = $conn->query($subjectsQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add and View Subjects</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function printTable() {
            var printContent = document.getElementById('subjectTable').outerHTML;
            var originalContent = document.body.innerHTML;
            document.body.innerHTML = "<h1>All Subjects</h1>" + printContent;
            window.print();
            document.body.innerHTML = originalContent;
        }
    </script>
    <style>
        .footer { margin-top: auto; padding: 10px 0; background-color: #343a40; color: white; text-align: center; }
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
    <h1>Add or Update Subject</h1>
    <form method="POST" action="add_subject.php">
        <div class="mb-3">
            <label for="subject_name" class="form-label">Subject Name</label>
            <input type="text" name="subject_name" id="subject_name" class="form-control" required placeholder="e.g. Eng@!English">
            <small class="form-text text-muted">To update an existing subject, use the format: existing_subject@!new_subject</small>
        </div>
        <div class="mb-3">
            <label for="subject_type" class="form-label">Subject Type</label>
            <select name="subject_type" id="subject_type" class="form-control" required>
                <option value="core">Core</option>
                <option value="elective">Elective</option>
            </select>
        </div>
        <button type="submit" name="add_subject" class="btn btn-primary">Add Subject</button>
    </form>

    <hr>

    <h2>All Subjects</h2>
    <button onclick="printTable()" class="btn btn-secondary mb-3">Print</button>

    <table class="table table-bordered" id="subjectTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Subject Name</th>
                <th>Type</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $count = 1;
            while ($subject = $subjectsResult->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $count++; ?></td>
                    <td><?php echo htmlspecialchars($subject['subject_name']); ?></td>
                    <td>
                        <?php 
                        echo $subject['is_core'] == 1 ? 'Core' : 'Elective'; 
                        ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
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
