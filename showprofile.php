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

// Check if 'username' is set in the query parameters
if (isset($_GET['username'])) {
    $username = $_GET['username'];

    // Prepare and execute SQL query to fetch user details by username
    $sql = "SELECT id, name, email, genre, books FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch user details
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Store user details in variables to pass to showprofile.html
        $name = htmlspecialchars($user['name']);
        $email = htmlspecialchars($user['email']);
        $genre = htmlspecialchars($user['genre']);
        $books = htmlspecialchars($user['books']);
        $receiver_id = $user['id'];
    } else {
        // Handle case where no user is found
        $name = "Not available";
        $email = "Not available";
        $genre = "Not available";
        $books = "Not available";
        $receiver_id = null;
    }

    // Close the statement
    $stmt->close();
} else {
    echo "No username provided in the URL.<br>";
}

// Close the connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #ff9ff3, #48dbfb, #1dd1a1);
            background-size: 300% 300%;
            animation: backgroundShift 15s ease infinite;
        }

        @keyframes backgroundShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .profile-container {
            width: 90%;
            max-width: 600px;
            padding: 30px;
            background-color: rgba(255, 255, 255, 0.85);
            border-radius: 20px;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.2);
            text-align: center;
            animation: fadeIn 2s ease-out;
            position: relative;
        }

        h2 {
            color: #2e86de;
            font-family: 'Playfair Display', serif;
            margin-bottom: 20px;
            font-size: 2.5rem;
            letter-spacing: 1px;
        }

        .profile-details {
            text-align: left;
            margin-bottom: 20px;
        }

        .profile-details p {
            font-size: 1.2rem;
            color: #555;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
            transition: color 0.3s ease;
        }

        .profile-details p:hover {
            color: #2e86de;
        }

        .btn-connection {
            padding: 15px 25px;
            background-color: #1dd1a1;
            border: none;
            color: white;
            border-radius: 50px;
            font-size: 1.1rem;
            cursor: pointer;
            box-shadow: 0 0 10px rgba(0, 255, 100, 0.6), 0 0 20px rgba(0, 255, 100, 0.8);
            transition: all 0.4s ease;
            text-transform: uppercase;
        }

        .btn-connection:hover {
            background-color: #10ac84;
            transform: scale(1.1);
            box-shadow: 0 0 20px rgba(0, 200, 100, 0.9), 0 0 40px rgba(0, 200, 100, 1);
        }

        @keyframes fadeIn {
            0% { opacity: 0; transform: translateY(30px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 600px) {
            .profile-container {
                padding: 20px;
            }

            h2 {
                font-size: 2rem;
            }

            .profile-details p {
                font-size: 1rem;
            }

            .btn-connection {
                padding: 12px 20px;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <h2>Profile Details</h2>
        <div class="profile-details">
            <p><strong>Name:</strong> <?php echo $name; ?></p>
            <p><strong>Email:</strong> <?php echo $email; ?></p>
            <p><strong>Favorite Genre:</strong> <?php echo $genre; ?></p>
            <p><strong>Favorite Books:</strong> <?php echo $books; ?></p>
        </div>
        <form method="POST" action="connection.php">
            <input type="hidden" name="sender_id" value="<?php echo $_SESSION['user_id']; ?>">
            <input type="hidden" name="receiver_id" value="<?php echo $receiver_id; ?>">
            <button type="submit" class="btn-connection">Send Connection Request</button>
        </form>
    </div>
</body>
</html>
