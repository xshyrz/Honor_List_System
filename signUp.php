<?php
include __DIR__ . '/database/db_conn.php';   // PDO PostgreSQL connection

$error = array();

if (isset($_POST['submit'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_pass = $_POST['confirm_pass'];

    if ($password !== $confirm_pass) {
        $error[] = 'Password not matched!';
    } else {
        // Check if username already exists
        $check_query = "SELECT id FROM signup WHERE username = :username";
        $stmt_check = $conn->prepare($check_query);
        $stmt_check->execute([':username' => $username]);
        
        if ($stmt_check->rowCount() > 0) {
            $error[] = 'Username already exists!';
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $insert_query = "INSERT INTO signup (username, email, password) 
                             VALUES (:username, :email, :password)";
            $stmt_insert = $conn->prepare($insert_query);
            $result = $stmt_insert->execute([
                ':username' => $username,
                ':email' => $email,
                ':password' => $hashed_password
            ]);

            if ($result) {
                header('Location: login.php');
                exit();
            } else {
                $error[] = 'Error creating user!';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Sign Up | Honor List System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../css/signUp.css"> <!-- (../) get the root folder -->
</head>
<body>

<div class="main-container">
    <div class="form-card">
        <h2>Create Account</h2>

        <?php if(!empty($error)): ?>
            <div class="error-msg">
                <?php foreach($error as $err): ?>
                    <?= htmlspecialchars($err) ?><br>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="username" placeholder="Username" required>
            </div>
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="Email Address" required>
            </div>
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <div class="input-group">
                <i class="fas fa-check-circle"></i>
                <input type="password" name="confirm_pass" placeholder="Confirm Password" required>
            </div>

            <button type="submit" name="submit" class="btn-submit">Sign Up</button>
            <div class="login-link">
                Already have an account? <a href="login.php">Login</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>