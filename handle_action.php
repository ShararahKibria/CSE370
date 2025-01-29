<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['connection_id'], $_POST['action'])) {
    if (!isset($_SESSION['user_id'])) {
        die("Unauthorized access. Please log in.");
    }

    $connection_id = $_POST['connection_id'];
    $action = $_POST['action'];

    // Database connection
    $mysqli = new mysqli('localhost', 'root', '', 'booklovertest');
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }

    // Prepare the SQL query based on the action
    if ($action == 'accept') {
        $sql = "UPDATE connections SET status = 'accepted', accepted_at = NOW() WHERE id = ?";
    } elseif ($action == 'reject') {
        $sql = "UPDATE connections SET status = 'rejected' WHERE id = ?";
    } else {
        echo "Invalid action.";
        exit();
    }

    // Execute the statement
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $connection_id);
    
    if ($stmt->execute()) {
        echo $action . " successfully"; // Return success message
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $mysqli->close();
} else {
    echo "Invalid request.";
}
?>
