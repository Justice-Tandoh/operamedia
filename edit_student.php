<?php
session_start();
include 'db.php';

if (!isset($_SESSION['class'])) {
    header("Location: index.php");
    exit();
}

$class = $_SESSION['class'];

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Fetch student data
    $studentQuery = "SELECT * FROM students WHERE id = '$id' LIMIT 1";
    $studentResult = $conn->query($studentQuery);
    $student = $studentResult->fetch_assoc();

    if (!$student) {
        echo "Student not found.";
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];

    $updateQuery = "UPDATE students SET first_name = '$first_name', last_name = '$last_name' WHERE id = '$id'";

    if ($conn->query($updateQuery) === TRUE) {
        echo "Student updated successfully!";
        header("Location: add_student.php");
        exit();
    } else {
        echo "Error updating record: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Student</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h2 class="text-center my-4">Edit Student (ID: <?php echo $student['id']; ?>)</h2>
        <form method="post" action="edit_student.php?id=<?php echo $student['id']; ?>">
            <div class="mb-3">
                <label for="first_name" class="form-label">First Name:</label>
                <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($student['first_name']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="last_name" class="form-label">Last Name:</label>
                <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($student['last_name']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Student</button>
        </form>
    </div>
</body>
</html>
