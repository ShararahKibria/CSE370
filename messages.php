<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch friends
$user_id = $_SESSION['user_id'];
$friends = $mysqli->query("SELECT friend_id FROM friends WHERE user_id = $user_id");
$friend_ids = [];
while ($row = $friends->fetch_assoc()) {
    $friend_ids[] = $row['friend_id'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $receiver_id = $_POST['receiver_id'];
    $message_content = $_POST['message_content'];

    $stmt = $mysqli->prepare("INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $user_id, $receiver_id, $message_content);
    
    if ($stmt->execute()) {
        header("Location: messages.php");
        exit();
    }
}

// Fetch messages
$messages = $mysqli->query("SELECT messages.content, users.username AS sender FROM messages JOIN users ON messages.sender_id = users.id WHERE receiver_id = $user_id ORDER BY messages.created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>Messages</title>
</head>
<body>
    <div class="container">
        <h2>Your Messages</h2>
        <?php while ($message = $messages->fetch_assoc()): ?>
            <p><strong><?php echo $message['sender']; ?>:</strong> <?php echo $message['content']; ?></p>
        <?php endwhile; ?>

        <h3>Send a Message</h3>
        <form method="POST" action="">
            <select name="receiver_id" required>
                <option value="">Select a friend</option>
                <?php foreach ($friend_ids as $friend_id): ?>
                    <?php
                    // Get friend username
                    $friend = $mysqli->query("SELECT username FROM users WHERE id = $friend_id")->fetch_assoc();
                    ?>
                    <option value="<?php echo $friend_id; ?>"><?php echo $friend['username']; ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="message_content" placeholder="Message" required>
            <button type="submit">Send</button>
        </form>
    </div>
</body>
</html>
