<?php
session_start();
include __DIR__ . '/database/db_conn.php';   // PDO PostgreSQL connection

$error = array();

if (isset($_POST['submit'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Fetch user by username
    $query = "SELECT * FROM signup WHERE username = :username";
    $stmt = $conn->prepare($query);
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['username'] = $user['username'];
        header('Location: viewOrReg.html');
        exit();
    } else {
        $error[] = 'Invalid username or password!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Login | Honor List System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../css/login.css"> <!-- (../) get the root folder -->
    <link rel="icon" type="image/png" href="../Images/lnhslogo.jpg">
</head>
<body>

<div class="main-container">
    <div class="form-card">
        <h2>Welcome Back</h2>

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
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <div class="forgot-link">
                <a href="forgot_password.php">Forgot Password?</a>
            </div>
            <button type="submit" name="submit" class="btn-submit">Login</button>
            <div class="signup-link">
                Don't have an account? <a href="signUp.php">Sign Up</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>