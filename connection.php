<?php
session_start(); // Start the session to access session variables

// Database credentials
$host = 'localhost';
$db = 'booklovertest';
$user = 'root';
$pass = '';

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form data is posted
if (isset($_POST['sender_id']) && isset($_POST['receiver_id'])) {
    $sender_id = $_POST['sender_id'];
    $receiver_id = $_POST['receiver_id'];

    // Prepare and execute SQL query to insert connection request
    $sql = "INSERT INTO connections (sender_id, receiver_id, status) VALUES (?, ?, 'pending')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $sender_id, $receiver_id);
    
    if ($stmt->execute()) {
        // Redirect to success page with sender_id
        header("Location: success.html?user_id=" . $sender_id);
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
} else {
    echo "Invalid request.";
}

// Close the connection
$conn->close();
?>
