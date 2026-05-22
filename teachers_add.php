<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include __DIR__ . '/database/db_conn.php';

$error = array();

if (isset($_POST['submit'])) {
    $teacher_id = $_POST['teacher_id'];

    $select = "SELECT * FROM teachers_id WHERE teachers_id = :teacher_id";
    $stmt = $conn->prepare($select);
    $stmt->execute([':teacher_id' => $teacher_id]);

    if ($stmt->rowCount() > 0) {
        header('location: register_student.php');
        exit();
    } else {
        $error[] = 'Teacher ID not found!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>HLS | Teacher's Access</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" type="image/png" href="../Images/h1Pic.png">
    <link rel="stylesheet" href="../css/teachers_add.css"> <!-- (../) get the root folder -->
</head>
<body>

<div class="main-container">
    <div class="form-card">
        <h2><i class="fas fa-chalkboard-teacher"></i> Teacher's ID</h2>
        <p>For teacher's only – verify your ID to add a new student.</p>

        <?php if (!empty($error)): ?>
            <div class="error-msg"><?= htmlspecialchars($error[0]) ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="input-group">
                <i class="fas fa-id-card"></i>
                <input type="number" name="teacher_id" placeholder="Enter your Teacher ID" required>
            </div>
            <button type="submit" name="submit" class="btn-submit">SUBMIT</button>
        </form>
    </div>
</div>

</body>
</html>