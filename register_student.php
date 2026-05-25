<?php
// (__DIR__) - gives C:\xampp\htdocs\Honor_List_System
// (/database) - goes up to database folder and then db_conn.php
include __DIR__ . '/database/db_conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    // Get form data
    $students_id = $_POST['students_id'];
    $fullname = $_POST['fullname'];
    $birthdate = $_POST['birthdate'];
    $formatted_birthdate = date('Y-m-d', strtotime($birthdate));
    $email = $_POST['email'];
    $gender = $_POST['gender'];
    $age = $_POST['age'];
    $strand = $_POST['strand'];      // e.g., 'ABM', 'STEM', 'HUMSS', etc.
    $average = $_POST['average'];
    $year = $_POST['year'];           // grade 11 or 12

    // Determine remarks
    function checkHonorStatus($average) {
        if ($average >= 98 && $average <= 100) {
            return "With Highest Honor";
        } elseif ($average >= 95 && $average <= 97) {
            return "With High Honor";
        } elseif ($average >= 90 && $average <= 94) {
            return "With Honor";
        } elseif ($average >= 85 && $average <= 89) {
            return "V-Satisfactory";
        } elseif ($average >= 80 && $average <= 84) {
            return "Satisfactory";
        } elseif ($average >= 75 && $average <= 79) {
            return "Needs Improvement";
        } else {
            return "Failed";
        }
    }

    $remarks = checkHonorStatus($average);

    try {
        // Insert into main register table
        $sql_register = "INSERT INTO register 
                         (students_id, fullname, birthdate, email, gender, age, strand, average, year, remarks)
                         VALUES (:students_id, :fullname, :birthdate, :email, :gender, :age, :strand, :average, :year, :remarks)";
        $stmt_reg = $conn->prepare($sql_register);
        $stmt_reg->execute([
            ':students_id' => $students_id,
            ':fullname' => $fullname,
            ':birthdate' => $formatted_birthdate,
            ':email' => $email,
            ':gender' => $gender,
            ':age' => $age,
            ':strand' => $strand,
            ':average' => $average,
            ':year' => $year,
            ':remarks' => $remarks
        ]);

        // If student qualifies for honor list (average >= 90)
        if ($average >= 90) {
            $honor_table = 'honor_' . strtolower($strand);   // e.g., honor_abm, honor_stem
            $sql_honor = "INSERT INTO $honor_table (students_id, fullname, average, year, remarks)
                          VALUES (:students_id, :fullname, :average, :year, :remarks)";
            $stmt_hon = $conn->prepare($sql_honor);
            $stmt_hon->execute([
                ':students_id' => $students_id,
                ':fullname' => $fullname,
                ':average' => $average,
                ':year' => $year,
                ':remarks' => $remarks
            ]);
        }

        // Redirect back to the honor list page of the selected strand
        $strand_lower = strtolower($strand);
        header("Location: /view_honorlist/honor_{$strand_lower}.php?msg=success");
        exit;

    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>HLS | Register Student</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" type="image/png" href="../Images/h1Pic.png">
    <link rel="stylesheet" href="../css/reg_student.css"> <!-- (../) get the root folder -->
</head>
<body>

<!-- Navigation Bar -->
<div class="navbar">
    <div class="logo-area">
        <img class="logo-img" src="../Images/logo.png" alt="logo">
        <span class="logo-text">Liloan National High School</span>
    </div>
    <ul class="nav-links">
        <li><a href="/navigation/home.html">Home</a></li>
        <li><a href="/navigation/about.html">About</a></li>
        <li><a href="/navigation/contact.html">Contact</a></li>
        <li><a href="/navigation/help.html">Help</a></li>
    </ul>
</div>

<div class="main-container">
    <!-- Hero section -->
    <div class="hero">
        <h1>✨ Register New Student ✨</h1>
        <p>Honor List System <br> Senior High School Department</p>
    </div>

    <!-- Registration Form -->
    <div class="form-card">
        <form method="post">
            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Full Name</label>
                    <input type="text" name="fullname" placeholder="Student's Fullname" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-id-card"></i> Student ID</label>
                    <input type="number" name="students_id" placeholder="8-digit ID" value="<?php echo isset($students_id) ? htmlspecialchars($students_id) : ''; ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-calendar-alt"></i> Birthdate</label>
                    <input type="date" name="birthdate" value="<?php echo isset($formatted_birthdate) ? $formatted_birthdate : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Email Address</label>
                    <input type="email" name="email" placeholder="student@gmail.com" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-fingerprint"></i> Age</label>
                    <input type="number" name="age" placeholder="e.g., 17, 18" value="<?php echo isset($age) ? htmlspecialchars($age) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-graduation-cap"></i> Strand</label>
                    <select name="strand" required>
                        <option value="ABM" <?php if(isset($strand) && $strand == 'ABM') echo 'selected'; ?>>Accountancy, Business & Management</option>
                        <option value="HUMSS" <?php if(isset($strand) && $strand == 'HUMSS') echo 'selected'; ?>>Humanities and Social Sciences</option>
                        <option value="STEM" <?php if(isset($strand) && $strand == 'STEM') echo 'selected'; ?>>Science, Technology, Engineering, and Mathematics</option>
                        <option value="ICT" <?php if(isset($strand) && $strand == 'ICT') echo 'selected'; ?>>Information and Communication Technology</option>
                        <option value="EIM" <?php if(isset($strand) && $strand == 'EIM') echo 'selected'; ?>>Electrical Installation Management</option>
                        <option value="HE" <?php if(isset($strand) && $strand == 'HE') echo 'selected'; ?>>Home Economics</option>
                        <option value="PLUMBING" <?php if(isset($strand) && $strand == 'PLUMBING') echo 'selected'; ?>>Plumbing Technology</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-chart-line"></i> Average</label>
                    <input type="number" step="0.01" name="average" placeholder="e.g., 94.5, 89" value="<?php echo isset($average) ? htmlspecialchars($average) : ''; ?>" required>
                </div>
                <div class="form-group"><br>
                    <label><i class="fas fa-venus-mars"></i> Gender</label>
                    <div class="radio-group">
                        <div>
                            <input type="radio" id="male" name="gender" value="Male" <?php if(isset($gender) && $gender === 'Male') echo 'checked'; ?>>
                            <label for="male">Male</label>
                        </div>
                        <div>
                            <input type="radio" id="female" name="gender" value="Female" <?php if(isset($gender) && $gender === 'Female') echo 'checked'; ?>>
                            <label for="female">Female</label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-calendar-week"></i> Year Level</label>
                    <select name="year">
                        <option value="11" <?php if(isset($year) && $year == '11') echo 'selected'; ?>>Grade 11</option>
                        <option value="12" <?php if(isset($year) && $year == '12') echo 'selected'; ?>>Grade 12</option>
                    </select>
                </div>
            </div>

            <div class="button-group">
                <button type="submit" name="submit" class="btn-submit">
                    <i class="fas fa-save"></i> SUBMIT
                </button>
                <a href="view_all.php" class="btn-cancel">
                    <i class="fas fa-times"></i> CANCEL
                </a>
            </div>
        </form>
    </div>
</div>

<div class="footer">
    <p>&copy; Liloan National High School - Senior High School Department. All rights reserved.</p>
</div>

</body>
</html>