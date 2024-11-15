<?php
session_start();
include 'db.php';

$class = $_SESSION['class'];
$sql = "SELECT * FROM students WHERE class = '$class'";
$result = $conn->query($sql);

echo "<table class='table table-bordered'><thead><tr><th>Student Name</th></tr></thead><tbody>";
while ($row = $result->fetch_assoc()) {
    echo "<tr><td>" . $row['name'] . "</td></tr>";
}
echo "</tbody></table>";

$conn->close();
?>
