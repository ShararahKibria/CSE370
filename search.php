<?php
// Database credentials
$host = 'localhost'; // Database host
$db = 'booklovertest'; // Your database name
$user = 'root'; // Database username
$pass = ''; // Database password

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if query is set
if (isset($_GET['query'])) {
    $query = $_GET['query'];

    // Prepare and execute the SQL query
    $sql = "SELECT username, name, genre, books FROM users WHERE 
            username LIKE ? OR name LIKE ? OR genre LIKE ? OR books LIKE ?";
    $param = "%" . $query . "%";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $param, $param, $param, $param);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch results and display them
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<div onclick="selectSuggestion(\'' . htmlspecialchars($row['username']) . '\')">';
            echo '<a href="showprofile.php?username=' . urlencode($row['username']) . '">';
            echo '<strong>' . htmlspecialchars($row['username']) . '</strong> (' . htmlspecialchars($row['name']) . ')<br>';
            echo 'Genre: ' . htmlspecialchars($row['genre']) . '<br>';
            echo 'Books: ' . htmlspecialchars($row['books']);
            echo '</a>';
            echo '</div>';
        }
    } else {
        echo '<div>No results found</div>';
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
}
?>
