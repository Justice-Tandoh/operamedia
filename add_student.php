<?php
session_start();
include 'db.php';

if (!isset($_SESSION['class'])) {
    header("Location: index.php");
    exit();
}

$class = $_SESSION['class'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];

    $sql = "INSERT INTO students (first_name, last_name, class) VALUES ('$first_name', '$last_name', '$class')";

    if ($conn->query($sql) === TRUE) {
        echo "Student added successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Fetch students list for this class
$studentsQuery = "SELECT * FROM students WHERE class = '$class'";
$studentsResult = $conn->query($studentsQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Student</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { display: flex; flex-direction: column; min-height: 100vh; background-color: #f8f9fa; }
        .container { margin-top: 20px; }
        .footer { margin-top: auto; padding: 10px 0; background-color: #343a40; color: white; text-align: center; }
        .table-container { margin-top: 20px; }
        .btn { margin: 5px; }
        th, td { color: white; background-color: black; }

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
            .navbar, .footer, .btn {
                display: none;
            }

            /* Watermark */
            .printable-table::before {
                content: 'Opera Media Solutions';
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                font-size: 100px;
                color: rgba(0, 0, 0, 0.1);
                z-index: -1;
                white-space: nowrap;
            }

            /* Ensure Footer in Print */
            .footer {
                position: absolute;
                bottom: 0;
                width: 100%;
                text-align: center;
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

    <!-- Add Student Form -->
    <div class="container">
        <h2 class="text-center my-4">Register Student</h2>
        <form method="post" action="add_student.php">
            <div class="mb-3">
                <label for="first_name" class="form-label">First Name:</label>
                <input type="text" class="form-control" id="first_name" name="first_name" required>
            </div>
            <div class="mb-3">
                <label for="last_name" class="form-label">Last Name:</label>
                <input type="text" class="form-control" id="last_name" name="last_name" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Student</button>
        </form>
        
        <!-- Students List Table -->
        <div class="table-container">
            <h3 class="text-center my-4">Student List (Class: <?php echo $class; ?>)</h3>
            <table class="table table-bordered printable-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $count = 1;
                    while ($row = $studentsResult->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $count++; ?></td>
                            <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <button onclick="window.print()" class="btn btn-success">Print Table</button>
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
