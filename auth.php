<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['username'] = $user['username'];
        $_SESSION['class'] = $user['class'] ?? null; // Set class to null if not found

        // Check if the class is set, then redirect
        if ($_SESSION['class']) {
            header("Location: dashboard.php");
            exit();
        } else {
            echo "Class not found for this user.";
        }
    } else {
        echo "Invalid username or password.";
    }
}
?>
