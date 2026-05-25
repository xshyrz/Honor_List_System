<?php
session_start();
include __DIR__ . '/database/db_conn.php';

// Allowed strands
$allowed_strands = ['abm', 'stem', 'humss', 'ict', 'eim', 'he', 'plumbing'];

// Get strand from URL (default to empty if not present)
$strand = isset($_GET['strand']) ? strtolower(trim($_GET['strand'])) : '';
if (!in_array($strand, $allowed_strands)) {
    die("Invalid strand. Please go back and select a valid strand.");
}

$honor_table = "honor_" . $strand;

$id = "";
$students_id = "";
$fullname = "";
$average = "";
$year = "";
$errorMessage = "";
$successMessage = "";

// GET request – load existing data
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET["id"])) {
        header("Location: view_honorlist/honor_{$strand}.php");
        exit;
    }
    $id = (int)$_GET["id"];

    $sql = "SELECT * FROM $honor_table WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        // No such record – go back to the honor list
        header("Location: view_honorlist/honor_{$strand}.php");
        exit;
    }

    $students_id = $row['students_id'];
    $fullname = $row['fullname'];
    $average = $row['average'];
    $year = $row['year'];
}
// POST request – update data
else {
    $id = (int)$_POST['id'];
    $students_id = (int)$_POST['students_id'];
    $fullname = trim($_POST['fullname']);
    $average = (float)$_POST['average'];
    $year = $_POST['year'];

    // Determine remarks based on average
    if ($average >= 98 && $average <= 100) {
        $remarks = "With Highest Honor";
    } elseif ($average >= 95 && $average <= 97) {
        $remarks = "With High Honor";
    } elseif ($average >= 90 && $average <= 94) {
        $remarks = "with Honor";
    } elseif ($average >= 85 && $average <= 89) {
        $remarks = "V-Satisfactory";
    } elseif ($average >= 80 && $average <= 84) {
        $remarks = "Satisfactory";
    } elseif ($average >= 75 && $average <= 79) {
        $remarks = "Needs Improvement";
    } else {
        $remarks = "Failed";
    }

    try {
        // Update register table
        $sql_reg = "UPDATE register SET fullname = :fullname, year = :year, average = :average, remarks = :remarks 
                    WHERE students_id = :students_id";
        $stmt_reg = $conn->prepare($sql_reg);
        $stmt_reg->execute([
            ':fullname' => $fullname,
            ':year' => $year,
            ':average' => $average,
            ':remarks' => $remarks,
            ':students_id' => $students_id
        ]);

        // Handle honor table
        if ($average >= 90) {
            // Check if record already exists in honor table
            $check_sql = "SELECT id FROM $honor_table WHERE students_id = :students_id";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->execute([':students_id' => $students_id]);
            $exists = $check_stmt->fetch();

            if ($exists) {
                // Update existing honor record
                $sql_honor = "UPDATE $honor_table SET fullname = :fullname, average = :average, year = :year, remarks = :remarks 
                              WHERE students_id = :students_id";
                $stmt_honor = $conn->prepare($sql_honor);
                $stmt_honor->execute([
                    ':fullname' => $fullname,
                    ':average' => $average,
                    ':year' => $year,
                    ':remarks' => $remarks,
                    ':students_id' => $students_id
                ]);
            } else {
                // Insert new honor record
                $sql_honor = "INSERT INTO $honor_table (students_id, fullname, average, year, remarks) 
                              VALUES (:students_id, :fullname, :average, :year, :remarks)";
                $stmt_honor = $conn->prepare($sql_honor);
                $stmt_honor->execute([
                    ':students_id' => $students_id,
                    ':fullname' => $fullname,
                    ':average' => $average,
                    ':year' => $year,
                    ':remarks' => $remarks
                ]);
            }
        } else {
            // Average < 90 – remove from honor table if present
            $del_sql = "DELETE FROM $honor_table WHERE students_id = :students_id";
            $del_stmt = $conn->prepare($del_sql);
            $del_stmt->execute([':students_id' => $students_id]);
        }

        // Redirect to the appropriate honor list page
        header("Location: view_honorlist/honor_{$strand}.php?msg=updated");
        exit;
    } catch (PDOException $e) {
        $errorMessage = "Database error: " . $e->getMessage();
    }
}

// Convert strand to display name
$strand_names = [
    'abm' => 'Accountancy, Business & Management',
    'stem' => 'Science, Technology, Engineering & Mathematics',
    'humss' => 'Humanities & Social Sciences',
    'ict' => 'Information & Communication Technology',
    'eim' => 'Electrical Installation & Maintenance',
    'he' => 'Home Economics',
    'plumbing' => 'Plumbing'
];
$strand_display = $strand_names[$strand] ?? ucfirst($strand);
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
    <link rel="stylesheet" href="../css/edit_student.css"> <!-- (../) get the root folder -->
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
        <p><?= htmlspecialchars($strand_display) ?> Strand</p>

        <?php if ($errorMessage): ?>
            <div class="error-msg"><?= htmlspecialchars($errorMessage) ?></div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">

            <div class="row-2col">
                <div class="input-group">
                    <label><i class="fas fa-user"></i> Full Name</label>
                    <input type="text" name="fullname" value="<?= htmlspecialchars($fullname) ?>" required>
                </div>
                <div class="input-group">
                    <label><i class="fas fa-id-card"></i> Student ID</label>
                    <input type="number" name="students_id" value="<?= htmlspecialchars($students_id) ?>" required>
                </div>
            </div>

            <div class="row-2col">
                <div class="input-group">
                    <label><i class="fas fa-calendar-week"></i> Year Level</label>
                    <select name="year">
                        <option value="11" <?= ($year == '11') ? 'selected' : '' ?>>Grade 11</option>
                        <option value="12" <?= ($year == '12') ? 'selected' : '' ?>>Grade 12</option>
                    </select>
                </div>
                <div class="input-group">
                    <label><i class="fas fa-chart-line"></i> Average</label>
                    <input type="number" step="0.01" name="average" value="<?= htmlspecialchars($average) ?>" required>
                </div>
            </div>

            <div class="button-group">
                <button type="submit" name="submit" class="btn-submit">
                    <i class="fas fa-save"></i> UPDATE</button>
                <div style="text-align: center;">
                    <a href="view_honorlist/honor_<?= $strand ?>.php" class="btn-cancel"><i class="fas fa-times"></i> CANCEL</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="footer">
    <p>&copy; Liloan National High School – Senior High School Department. All rights reserved.</p>
</div>

</body>
</html>