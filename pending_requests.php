<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access. Please log in.");
}

$user_id = $_SESSION['user_id'];

// Database connection
$mysqli = new mysqli('localhost', 'root', '', 'booklovertest');
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Fetch pending requests for the logged-in user (receiver)
$sql = "SELECT connections.id, users.name, users.username, connections.requested_at 
        FROM connections
        JOIN users ON connections.sender_id = users.id
        WHERE connections.receiver_id = ? AND connections.status = 'pending'";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<table>";
    echo "<tr><th>Sender</th><th>Username</th><th>Requested At</th><th>Action</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
        echo "<td>" . htmlspecialchars($row['requested_at']) . "</td>";
        echo "<td>
            <button class='accept-button' data-id='" . $row['id'] . "'>Accept</button>
            <button class='reject-button' data-id='" . $row['id'] . "'>Reject</button>
        </td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No pending requests.</p>";
}

$mysqli->close();
?>
