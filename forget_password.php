<?php
include 'db.php';

$showPasswordForm = false; // Flag to control showing the password update form
$username = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['recovery_word'])) {
    // Step 1: Verify recovery word
    $username = $_POST['username'];
    $recovery_word = $_POST['recovery_word'];

    $sql = "SELECT * FROM users WHERE username = '$username' AND recovery_word = '$recovery_word'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Recovery word verified, show the password update form
        $showPasswordForm = true;
    } else {
        echo "Incorrect recovery word.";
    }
} elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_password'])) {
    // Step 2: Update password
    $new_password = password_hash($_POST['new_password'], PASSWORD_BCRYPT);
    $username = $_POST['username'];

    $sql = "UPDATE users SET password = '$new_password' WHERE username = '$username'";
    if ($conn->query($sql) === TRUE) {
        header("Location: index.php");
        exit();
    } else {
        echo "Error updating password: " . $conn->error;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <style>
        /* Styling as previously defined */
        body { font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; background-color: #f4f4f4; }
        .container { width: 300px; padding: 20px; background-color: white; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); border-radius: 8px; text-align: center; }
        .container h2 { margin-bottom: 20px; }
        .container label, .container input, .container button { width: 100%; margin-top: 10px; display: block; text-align: left; }
        .container button { background-color: #ffc107; color: white; border: none; padding: 10px; border-radius: 4px; cursor: pointer; }
        .container a { text-decoration: none; color: #007bff; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Forgot Password</h2>

        <?php if (!$showPasswordForm): ?>
            <!-- Step 1: Verify recovery word form -->
            <form action="forget_password.php" method="post">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
                
                <label for="recovery_word">Recovery Word:</label>
                <input type="text" id="recovery_word" name="recovery_word" required>
                
                <button type="submit">Submit</button>
            </form>
        <?php else: ?>
            <!-- Step 2: Update password form -->
            <form action="forget_password.php" method="post">
                <input type="hidden" name="username" value="<?php echo htmlspecialchars($username); ?>">
                
                <label for="new_password">Enter New Password:</label>
                <input type="password" id="new_password" name="new_password" required>
                
                <label for="confirm_new_password">Confirm New Password:</label>
                <input type="password" id="confirm_new_password" name="confirm_new_password" required>
                
                <button type="submit">Update</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
