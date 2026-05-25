<?php
session_start();
include __DIR__ . '/database/db_conn.php';

$errorMessage = "";
$student = null;
$students_id = 0;

// Get students_id from URL or POST
if (isset($_GET['students_id'])) {
    $students_id = (int)$_GET['students_id'];
} elseif (isset($_POST['students_id'])) {
    $students_id = (int)$_POST['students_id'];
} else {
    header("Location: view_all.php");
    exit;
}

// Load current data from register
$query = "SELECT * FROM register WHERE students_id = :students_id";
$stmt = $conn->prepare($query);
$stmt->execute([':students_id' => $students_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$student) {
    header("Location: view_all.php");
    exit;
}

// Pre-fill current values
$orig_students_id = $student['students_id'];
$fullname = $student['fullname'];
$birthdate = $student['birthdate'];
$email = $student['email'];
$gender = $student['gender'];
$age = $student['age'];
$strand = $student['strand'];
$average = $student['average'];
$year = $student['year'];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $new_students_id = (int)$_POST['students_id'];
    $new_fullname = trim($_POST['fullname']);
    $new_birthdate = $_POST['birthdate'];
    $new_email = trim($_POST['email']);
    $new_gender = $_POST['gender'];
    $new_age = (int)$_POST['age'];
    $new_strand = $_POST['strand'];
    $new_average = (float)$_POST['average'];
    $new_year = $_POST['year'];

    // Determine remarks
    if ($new_average >= 98 && $new_average <= 100) {
        $remarks = "With Highest Honor";
    } elseif ($new_average >= 95 && $new_average <= 97) {
        $remarks = "With High Honor";
    } elseif ($new_average >= 90 && $new_average <= 94) {
        $remarks = "with Honor";
    } elseif ($new_average >= 85 && $new_average <= 89) {
        $remarks = "V-Satisfactory";
    } elseif ($new_average >= 80 && $new_average <= 84) {
        $remarks = "Satisfactory";
    } elseif ($new_average >= 75 && $new_average <= 79) {
        $remarks = "Needs Improvement";
    } else {
        $remarks = "Failed";
    }

    try {
        // Begin transaction
        $conn->beginTransaction();

        // 1. Update register table
        $sql_reg = "UPDATE register SET 
                        students_id = :students_id,
                        fullname = :fullname,
                        birthdate = :birthdate,
                        email = :email,
                        gender = :gender,
                        age = :age,
                        strand = :strand,
                        average = :average,
                        year = :year,
                        remarks = :remarks
                    WHERE students_id = :old_students_id";
        $stmt_reg = $conn->prepare($sql_reg);
        $stmt_reg->execute([
            ':students_id' => $new_students_id,
            ':fullname' => $new_fullname,
            ':birthdate' => $new_birthdate,
            ':email' => $new_email,
            ':gender' => $new_gender,
            ':age' => $new_age,
            ':strand' => $new_strand,
            ':average' => $new_average,
            ':year' => $new_year,
            ':remarks' => $remarks,
            ':old_students_id' => $orig_students_id
        ]);

        // 2. Handle honor table changes
        $old_honor_table = "honor_" . strtolower($strand);
        $new_honor_table = "honor_" . strtolower($new_strand);

        // Remove from old honor table if present
        $del_old = "DELETE FROM $old_honor_table WHERE students_id = :students_id";
        $stmt_del = $conn->prepare($del_old);
        $stmt_del->execute([':students_id' => $orig_students_id]);

        // If average >= 90, insert into new honor table (or update if ID changed)
        if ($new_average >= 90) {
            // Check if already exists in new honor table (by new_students_id)
            $check_sql = "SELECT id FROM $new_honor_table WHERE students_id = :students_id";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->execute([':students_id' => $new_students_id]);
            $exists = $check_stmt->fetch();
            if ($exists) {
                $sql_honor = "UPDATE $new_honor_table SET 
                                fullname = :fullname, 
                                average = :average, 
                                year = :year, 
                                remarks = :remarks 
                              WHERE students_id = :students_id";
                $stmt_honor = $conn->prepare($sql_honor);
                $stmt_honor->execute([
                    ':fullname' => $new_fullname,
                    ':average' => $new_average,
                    ':year' => $new_year,
                    ':remarks' => $remarks,
                    ':students_id' => $new_students_id
                ]);
            } else {
                $sql_honor = "INSERT INTO $new_honor_table (students_id, fullname, average, year, remarks) 
                              VALUES (:students_id, :fullname, :average, :year, :remarks)";
                $stmt_honor = $conn->prepare($sql_honor);
                $stmt_honor->execute([
                    ':students_id' => $new_students_id,
                    ':fullname' => $new_fullname,
                    ':average' => $new_average,
                    ':year' => $new_year,
                    ':remarks' => $remarks
                ]);
            }
        }

        // Commit transaction
        $conn->commit();

        // Redirect back to all students page
        header("Location: view_all.php?msg=updated");
        exit;
    } catch (PDOException $e) {
        $conn->rollBack();
        $errorMessage = "Database error: " . $e->getMessage();
    }
}

// Strand display names and options for dropdown
$strand_options = [
    'ABM' => 'Accountancy, Business & Management',
    'STEM' => 'Science, Technology, Engineering & Mathematics',
    'HUMSS' => 'Humanities & Social Sciences',
    'ICT' => 'Information & Communication Technology',
    'EIM' => 'Electrical Installation & Maintenance',
    'HE' => 'Home Economics',
    'PLUMBING' => 'Plumbing'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Edit Student | Honor List System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" type="image/png" href="../Images/h1Pic.png">
    <link rel="stylesheet" href="../css/edit_register.css"> <!-- (../) get the root folder -->
</head>
<body>
<div class="navbar">
    <div class="logo-area">
        <img class="logo-img" src="Images/h1Pic.png" alt="logo">
        <span class="logo-text">HONOR LIST SYSTEM</span>
    </div>
    <ul class="nav-links">
        <li><a href="/navigation/home.html">Home</a></li>
        <li><a href="/navigation/about.html">About</a></li>
        <li><a href="/navigation/contact.html">Contact</a></li>
        <li><a href="/navigation/help.html">Help</a></li>
    </ul>
</div>
<div class="main-container">
    <div class="form-card">
        <h2><i class="fas fa-user-edit"></i> Edit Student Record</h2>
        <p>All fields except ID are editable</p>
        <?php if ($errorMessage): ?>
            <div class="error-msg"><?= htmlspecialchars($errorMessage) ?></div>
        <?php endif; ?>
        <form method="post">
            <!-- Row 1: Full Name and Student ID -->
            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Full Name</label>
                    <input type="text" name="fullname" value="<?= htmlspecialchars($fullname) ?>" required>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-id-card"></i> Student ID</label>
                    <input type="number" name="students_id" value="<?= htmlspecialchars($orig_students_id) ?>" required>
                </div>
            </div>
            <!-- Row 2: Birthdate and Email -->
            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-calendar-alt"></i> Birthdate</label>
                    <input type="date" name="birthdate" value="<?= htmlspecialchars($birthdate) ?>" required>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
                </div>
            </div>
            <!-- Row 3: Age and Strand -->
            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-fingerprint"></i> Age</label>
                    <input type="number" name="age" value="<?= htmlspecialchars($age) ?>" required>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-graduation-cap"></i> Strand</label>
                    <select name="strand" required>
                        <?php foreach ($strand_options as $key => $display): ?>
                            <option value="<?= $key ?>" <?= ($strand == $key) ? 'selected' : '' ?>><?= $display ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <!-- Row 4: Average, Gender, Year -->
            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-chart-line"></i> Average</label>
                    <input type="number" step="0.01" name="average" value="<?= htmlspecialchars($average) ?>" required>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-venus-mars"></i> Gender</label>
                    <div class="radio-group">
                        <div><input type="radio" id="male" name="gender" value="Male" <?= ($gender == 'Male') ? 'checked' : '' ?>><label for="male">Male</label></div>
                        <div><input type="radio" id="female" name="gender" value="Female" <?= ($gender == 'Female') ? 'checked' : '' ?>><label for="female">Female</label></div>
                    </div>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-calendar-week"></i> Year Level</label>
                    <select name="year">
                        <option value="11" <?= ($year == '11') ? 'selected' : '' ?>>Grade 11</option>
                        <option value="12" <?= ($year == '12') ? 'selected' : '' ?>>Grade 12</option>
                    </select>
                </div>
            </div>

            <div class="button-group">
                <button type="submit" name="submit" class="btn-submit">
                    <i class="fas fa-save"></i> UPDATE</button>
                <a href="view_all.php" class="btn-cancel">
                    <i class="fas fa-times"></i> CANCEL
                </a>
            </div>
        </form>
    </div>
</div>
<div class="footer">
    <p>&copy; Liloan National High School – Senior High School Department. All rights reserved.</p>
</div>
</body>
</html>