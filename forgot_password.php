<?php
session_start();
include __DIR__ . '/database/db_conn.php';

$error = '';
$message = '';
$step = 'request'; // 'request' or 'reset'
$user_id = null;

// Verify username/email
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['verify'])) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $query = "SELECT id, username FROM signup WHERE username = :username AND email = :email";
        $stmt = $conn->prepare($query);
        $stmt->execute([':username' => $username, ':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $_SESSION['reset_user_id'] = $user['id'];
            $_SESSION['reset_username'] = $user['username'];
            $step = 'reset';
        } else {
            $error = "Username and email do not match our records.";
        }
    } elseif (isset($_POST['reset']) && isset($_SESSION['reset_user_id'])) {
        $new_password = $_POST['new_password'];
        $confirm = $_POST['confirm_password'];
        if ($new_password !== $confirm) {
            $error = "Passwords do not match.";
        } elseif (strlen($new_password) < 6) {
            $error = "Password must be at least 6 characters.";
        } else {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $update = "UPDATE signup SET password = :pass WHERE id = :id";
            $stmt = $conn->prepare($update);
            $stmt->execute([':pass' => $hashed, ':id' => $_SESSION['reset_user_id']]);
            $message = "Password has been reset successfully. You can now login.";
            unset($_SESSION['reset_user_id'], $_SESSION['reset_username']);
            $step = 'done';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Forgot Password | Honor List System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../css/forgot_pass.css"> <!-- (../) get the root folder -->
</head>
<body>

<div class="main-container">
    <div class="form-card">
        <?php if($step === 'reset'): ?>
            <h2>Reset Password</h2>
            <p style="text-align:center; color:#3E3C6E; margin-bottom:16px;">Hello, <?= htmlspecialchars($_SESSION['reset_username']) ?></p>
            <?php if($error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="post">
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="new_password" placeholder="New Password (min. 6 characters)" required>
                </div>
                <div class="input-group">
                    <i class="fas fa-check-circle"></i>
                    <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
                </div>
                <button type="submit" name="reset" class="btn-submit">Update Password</button>
                <div class="back-link">
                    <a href="login.php">Cancel</a>
                </div>
            </form>
        <?php elseif($step === 'done'): ?>
            <h2>Password Reset</h2>
            <div class="message"><?= htmlspecialchars($message) ?></div>
            <div class="back-link">
                <a href="login.php">Go to Login</a>
            </div>
        <?php else: ?>
            <h2>Forgot Password</h2>
            <?php if($error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
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
                <button type="submit" name="verify" class="btn-submit">Verify Identity</button>
                <div class="back-link">
                    <a href="login.php">Back to Login</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

</body>
</html>