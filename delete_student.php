<?php
session_start();
include __DIR__ . '/database/db_conn.php';

$students_id = isset($_GET['students_id']) ? (int)$_GET['students_id'] : 0;
if ($students_id <= 0) {
    header("Location: view_all.php?msg=invalid_id");
    exit;
}

// Fetch student's strand before deletion
$query = "SELECT strand FROM register WHERE students_id = :students_id";
$stmt = $conn->prepare($query);
$stmt->execute([':students_id' => $students_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    header("Location: view_all.php?msg=not_found");
    exit;
}

$strand = strtolower($student['strand']);
$honor_table = "honor_" . $strand;

try {
    $conn->beginTransaction();

    // Delete from honor table (if exists)
    $sql_honor = "DELETE FROM $honor_table WHERE students_id = :students_id";
    $stmt_honor = $conn->prepare($sql_honor);
    $stmt_honor->execute([':students_id' => $students_id]);

    // Delete from register table
    $sql_reg = "DELETE FROM register WHERE students_id = :students_id";
    $stmt_reg = $conn->prepare($sql_reg);
    $stmt_reg->execute([':students_id' => $students_id]);

    $conn->commit();
    header("Location: view_all.php?msg=deleted");
    exit;
} catch (PDOException $e) {
    $conn->rollBack();
    header("Location: view_all.php?msg=error&error=" . urlencode($e->getMessage()));
    exit;
}
?>