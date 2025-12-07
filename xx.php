<?php
include __DIR__ . '/conn.php';
$querys = file_get_contents('database.sql');

if (!$querys) {
    die("Error: Could not read database.sql");
}

if ($conn->multi_query($querys)) {
    // Flush results for multi_query
    while ($conn->more_results() && $conn->next_result()) {
        // just move to next result
    }

    echo "Database imported successfully!";
} else {
    echo "Error during import: " . $conn->error;
}

$conn->close();
