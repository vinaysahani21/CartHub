<?php
include 'auth_check.php';
include '../config/db.php';

// 1. Fetch all users from the database
$query = "SELECT id, name, email, role, created_at FROM users ORDER BY id ASC";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    $delimiter = ",";
    $filename = "CartHub_Users_" . date('Y-m-d') . ".csv";

    // 2. Set headers to force download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '";');

    // 3. Open PHP output stream
    $f = fopen('php://output', 'w');

    // 4. Set Column Headers
    $fields = array('USER ID', 'FULL NAME', 'EMAIL ADDRESS', 'ACCOUNT ROLE', 'REGISTRATION DATE');
    fputcsv($f, $fields, $delimiter);

    // 5. Output each row of data
    while ($row = $result->fetch_assoc()) {
        $lineData = array(
            $row['id'], 
            $row['name'], 
            $row['email'], 
            strtoupper($row['role']), 
            date('d M Y', strtotime($row['created_at']))
        );
        fputcsv($f, $lineData, $delimiter);
    }

    // 6. Close the stream
    fclose($f);
    exit;
} else {
    die("No user data found to export.");
}
?>