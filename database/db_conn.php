<?php
$host = 'localhost';
$port = '5432';          // default PostgreSQL port
$dbname = 'honorlist';
$user = 'postgres';      // change to your PostgreSQL user
$password = 'sqlserver26';  // change to your password

try {
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname;user=$user;password=$password");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Use UTF‑8
    $conn->exec("SET NAMES 'UTF8'");
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>