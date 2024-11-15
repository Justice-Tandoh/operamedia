<?php
include 'db.php';

// Fetch available classes
$available_classes = [];
$sql = "SELECT DISTINCT class FROM users";
$result = $conn->query($sql);

$taken_classes = [];
while ($row = $result->fetch_assoc()) {
    $taken_classes[] = $row['class'];
}

// List of all possible classes
$all_classes = [
    "Nursery 1", "Nursery 2", "Kindergarten 1","Kindergarten 2", "Basic 1", "Basic 2", "Basic 3", "Basic 4", "Basic 5", 
    "Basic 6", "Basic 7", "Basic 8", "Basic 9"
];

// Filter out taken classes
$available_classes = array_diff($all_classes, $taken_classes);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $class = $_POST['class'];
    $recovery_word = $_POST['recovery_word'];

    if ($password !== $confirm_password) {
        echo "Passwords do not match.";
    } else {
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z0-9]).{6,}$/', $password)) {
            echo "Password must be at least 6 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character.";
        } else {
            $sql = "INSERT INTO users (username, password, class, recovery_word) VALUES ('$username', '$password', '$class', '$recovery_word')";
            if ($conn->query($sql) === TRUE) {
                header("Location: index.php");
                exit();
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up</title>
    <style>
        body { font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; background-color: #f4f4f4; }
        .container { width: 300px; padding: 20px; background-color: white; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); border-radius: 8px; text-align: center; }
        .container h2 { margin-bottom: 20px; }
        .container label, .container input, .container button { width: 100%; margin-top: 10px; display: block; text-align: left; }
        .container button { background-color: #ffc107; color: white; border: none; padding: 10px; border-radius: 4px; cursor: pointer; }
        .container a { text-decoration: none; color: #007bff; }
        .progress-bar { height: 10px; width: 100%; background-color: #e0e0e0; margin-top: 5px; }
        .progress { height: 10px; background-color: #ffc107; }
        .checkbox-container { display: flex; align-items: center; margin-top: 5px; }
        .checkbox-container input { margin-right: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Sign Up</h2>
        <form action="signup.php" method="post" onsubmit="return validateForm()">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required onkeyup="checkPasswordStrength()">
            <div class="progress-bar"><div id="progress" class="progress" style="width: 0;"></div></div>
            <small id="strength-text"></small>

            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <!-- Show Password Checkbox -->
            <div class="checkbox-container">
                <input type="checkbox" id="show_password" onclick="togglePasswordVisibility()">
                <label for="show_password">Show Password</label>
            </div>

            <label for="class">Class:</label>
            <select id="class" name="class" required>
                <option value="">Select Class</option>
                <?php foreach ($available_classes as $class): ?>
                    <option value="<?= $class ?>"><?= $class ?></option>
                <?php endforeach; ?>
            </select>

            <label for="recovery_word">Recovery Word:</label>
            <input type="text" id="recovery_word" name="recovery_word" required>

            <button type="submit">Sign Up</button>
        </form>
        <p>Have an account? <a href="index.php">Log In here.</a></p>
    </div>

    <script>
        function checkPasswordStrength() {
            const password = document.getElementById("password").value;
            const progress = document.getElementById("progress");
            const strengthText = document.getElementById("strength-text");

            let strength = 0;
            if (password.length >= 6) strength += 1;
            if (/[a-z]/.test(password)) strength += 1;
            if (/[A-Z]/.test(password)) strength += 1;
            if (/[0-9]/.test(password)) strength += 1;
            if (/[^a-zA-Z0-9]/.test(password)) strength += 1;

            let width = (strength / 5) * 100;
            progress.style.width = width + "%";

            if (strength < 3) {
                strengthText.textContent = "Weak";
                progress.style.backgroundColor = "red";
            } else if (strength === 3) {
                strengthText.textContent = "Moderate";
                progress.style.backgroundColor = "orange";
            } else if (strength === 4) {
                strengthText.textContent = "Good";
                progress.style.backgroundColor = "#ffc107";
            } else {
                strengthText.textContent = "Strong";
                progress.style.backgroundColor = "green";
            }
        }

        function togglePasswordVisibility() {
            const password = document.getElementById("password");
            const confirmPassword = document.getElementById("confirm_password");
            const type = password.type === "password" ? "text" : "password";
            password.type = type;
            confirmPassword.type = type;
        }

        function validateForm() {
            const password = document.getElementById("password").value;
            const confirmPassword = document.getElementById("confirm_password").value;

            if (password !== confirmPassword) {
                alert("Passwords do not match.");
                return false;
            }
            return true;
        }
    </script>
</body>
</html>
