<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>HLS | All Students</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" type="image/png" href="../Images/h1Pic.png">
    <link rel="stylesheet" href="../css/view_all.css"> <!-- (../) get the root folder -->
</head>
<body>

<!-- Navigation Bar -->
<div class="navbar">
    <div class="logo-area">
        <img class="logo-img" src="Images/logo.png" alt="logo">
        <span class="logo-text">Liloan National High School Roll</span>
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
        <h1>📋 All Students Roll</h1>
        <p>Complete list of Senior High School students – all strands</p>
    </div>

    <!-- Add new student -->
    <div class="add-stdnt">
        <a class="btn-add" href="teachers_add.php">
            <i class="fas fa-user-plus"></i> Add New Student
        </a>
    </div>

    <!-- Table -->
    <div class="table-wrapper">
        <table class="student-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Student ID</th>
                    <th>Full Name</th>
                    <th>Birthdate</th>
                    <th>Email</th>
                    <th>Gender</th>
                    <th>Age</th>
                    <th>Strand</th>
                    <th>Average</th>
                    <th>Year</th>
                    <th>Remarks</th>
                    <th>Actions</th>
                </tr>
            </thead>
            
            <tbody>
              <?php
              // Start buffering to prevent accidental output from db_conn.php
              ob_start();
              include __DIR__ . '/database/db_conn.php';
              ob_end_clean();

              try {
                  $sql = "SELECT * FROM register ORDER BY id DESC";
                  $stmt = $conn->query($sql);
                  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                  if (count($rows) === 0) {
                      echo '<tr><td colspan="12" style="text-align:center;">No records found</td></tr>';
                  } else {
                      foreach ($rows as $row) {
                          // Use a HEREDOC or just echo each cell cleanly
                          ?>
                          <tr>
                              <td><?= htmlspecialchars($row['id']) ?></td>
                              <td><?= htmlspecialchars($row['students_id']) ?></td>
                              <td><?= htmlspecialchars($row['fullname']) ?></td>
                              <td><?= htmlspecialchars($row['birthdate']) ?></td>
                              <td><?= htmlspecialchars($row['email']) ?></td>
                              <td><?= htmlspecialchars($row['gender']) ?></td>
                              <td><?= htmlspecialchars($row['age']) ?></td>
                              <td><?= htmlspecialchars($row['strand']) ?></td>
                              <td><strong><?= htmlspecialchars($row['average']) ?></strong></td>
                              <td><?= htmlspecialchars($row['year']) ?></td>
                              <td><?= htmlspecialchars($row['remarks']) ?></td>
                              <td class="action-cell">
                                  <a class="btn-edit" href="edit_register.php?students_id=<?= $row['students_id'] ?>">
                                      <i class="fas fa-edit"></i> Edit
                                  </a>
                                  <a class="btn-delete" href="delete_reg.php?students_id=<?= $row['students_id'] ?>" 
                                    onclick="return confirm('Delete this student permanently?')">
                                      <i class="fas fa-trash-alt"></i> Delete
                                  </a>
                              </td>
                          </tr>
                          <?php
                      }
                  }
              } catch (PDOException $e) {
                  echo '<tr><td colspan="12" style="text-align:center; padding:40px;">Error loading data: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
              }
              ?>
          </tbody>
        </table>
    </div>

    <!-- Button to view honor students -->
    <div class="bottom-button">
        <a href="view.html" class="btn-honor">
            <i class="fas fa-trophy"></i> SEE HONOR STUDENTS
        </a>
    </div>
</div>

<div class="footer">
    <p>&copy; Liloan National High School - Senior High School Department. All rights reserved.</p>
</div>

</body>
</html>