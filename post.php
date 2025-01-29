<?php
session_start();
require 'db.php';

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle form submissions for creating a post, commenting, sending friend requests, and messaging
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // For creating a post
    if (isset($_POST['content'])) {
        $content = $_POST['content'];
        $stmt = $mysqli->prepare("INSERT INTO posts (user_id, content) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $content);
        $stmt->execute();
        $stmt->close(); // Close statement
        header("Location: post.php");
        exit();
    }
    // For commenting on a post
    elseif (isset($_POST['comment_content'])) {
        $post_id = $_POST['post_id'];
        $comment_content = $_POST['comment_content'];
        $stmt = $mysqli->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $post_id, $user_id, $comment_content);
        $stmt->execute();
        $stmt->close(); // Close statement
        header("Location: post.php");
        exit();
    }
    // For deleting a post
    elseif (isset($_POST['delete_post_id'])) {
        $delete_post_id = $_POST['delete_post_id'];
        $stmt = $mysqli->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $delete_post_id, $user_id);
        $stmt->execute();
        $stmt->close(); // Close statement
        header("Location: post.php");
        exit();
    }
    // For sending a friend request
    elseif (isset($_POST['friend_id'])) {
        $friend_id = $_POST['friend_id'];

        // Check if already friends
        $stmt = $mysqli->prepare("SELECT * FROM friends WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)");
        $stmt->bind_param("iiii", $user_id, $friend_id, $friend_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            // Check if the friend request already exists
            $stmt = $mysqli->prepare("SELECT * FROM friend_requests WHERE sender_id = ? AND receiver_id = ?");
            $stmt->bind_param("ii", $user_id, $friend_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 0) {
                // Insert friend request
                $stmt = $mysqli->prepare("INSERT INTO friend_requests (sender_id, receiver_id) VALUES (?, ?)");
                $stmt->bind_param("ii", $user_id, $friend_id);
                $stmt->execute();
                $stmt->close(); // Close statement
                echo "<p>Friend request sent!</p>";
            } else {
                echo "<p>Friend request already sent.</p>";
            }
        } else {
            echo "<p>You are already friends with this user.</p>";
        }
    }
    // For accepting a friend request
    elseif (isset($_POST['request_id'])) {
        $request_id = $_POST['request_id'];
        $stmt = $mysqli->prepare("SELECT sender_id FROM friend_requests WHERE id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $stmt->bind_result($sender_id);
        $stmt->fetch();
        $stmt->close(); // Close statement

        $stmt = $mysqli->prepare("INSERT INTO friends (user_id, friend_id) VALUES (?, ?), (?, ?)");
        $stmt->bind_param("iiii", $sender_id, $user_id, $user_id, $sender_id);
        $stmt->execute();
        $stmt->close(); // Close statement

        $stmt = $mysqli->prepare("DELETE FROM friend_requests WHERE id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $stmt->close(); // Close statement

        echo "<p>Friend request accepted!</p>";
    }
    // For sending a message
    elseif (isset($_POST['message_content']) && isset($_POST['receiver_id'])) {
        $receiver_id = $_POST['receiver_id'];
        $message_content = $_POST['message_content'];
        $stmt = $mysqli->prepare("INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $user_id, $receiver_id, $message_content);
        $stmt->execute();
        $stmt->close(); // Close statement
        echo "<p>Message sent!</p>";
    }
}

// Fetch posts with comments
$posts = $mysqli->query("SELECT posts.id, posts.content, posts.user_id, users.username FROM posts JOIN users ON posts.user_id = users.id ORDER BY posts.created_at DESC");

// Fetch all users with genre and favorite book for friend requests
$all_users = $mysqli->query("SELECT id, name, genre, favorite_book FROM users WHERE id != $user_id");

// Fetch friends with genre and favorite book for messaging
$friends = $mysqli->query("SELECT u.id, u.name, u.genre, u.favorite_book FROM users u JOIN friends f ON u.id = f.friend_id WHERE f.user_id = $user_id");

// Fetch incoming friend requests with genre and favorite book
$incoming_requests = $mysqli->query("SELECT fr.id, u.name AS sender_name, u.genre, u.favorite_book 
                                      FROM friend_requests fr 
                                      JOIN users u ON fr.sender_id = u.id 
                                      WHERE fr.receiver_id = $user_id");

// Fetch messages for display
$messages = $mysqli->query("SELECT m.content, u.name AS sender_name, m.created_at 
                             FROM messages m 
                             JOIN users u ON m.sender_id = u.id 
                             WHERE m.receiver_id = $user_id 
                             ORDER BY m.created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>Posts</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        h2 {
            color: #ff4081; /* Pink for Create Post */
            border-bottom: 2px solid #ff4081;
            padding-bottom: 10px;
        }
        h3 {
            border-bottom: 2px solid #4caf50; /* Green for sections */
            padding-bottom: 10px;
            margin-top: 20px;
            color: #4caf50;
        }
        textarea, input[type="text"], select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        textarea:focus, input[type="text"]:focus {
            border-color: #ff4081;
            outline: none;
        }
        button {
            background-color: #4caf50; /* Green for buttons */
            color: white;
            border: none;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background-color: #388e3c; /* Darker green on hover */
        }
        .post {
            background: #e3f2fd; /* Light blue for posts */
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            box-shadow: 0 1px 5px rgba(0, 0, 0, 0.1);
        }
        .comments {
            margin-top: 10px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .incoming-requests {
            background: #ffe0b2; /* Light orange for incoming requests */
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
        }
        .messages {
            background: #e1bee7; /* Light purple for messages */
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
        }
        .incoming-requests p, .messages p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Create Post</h2>
        <form method="POST" action="">
            <textarea name="content" placeholder="What's on your mind?" required></textarea>
            <button type="submit">Post</button>
        </form>

        <h3>Posts</h3>
        <?php while ($row = $posts->fetch_assoc()): ?>
            <div class="post">
                <h4><?php echo htmlspecialchars($row['username']); ?></h4>
                <p><?php echo htmlspecialchars($row['content']); ?></p>

                <div class="comments">
                    <h5>Comments</h5>
                    <form method="POST" action="">
                        <input type="hidden" name="post_id" value="<?php echo $row['id']; ?>">
                        <input type="text" name="comment_content" placeholder="Add a comment" required>
                        <button type="submit">Comment</button>
                    </form>
                    <?php
                    // Fetch comments for this post
                    $comments = $mysqli->query("SELECT comments.content, users.username FROM comments JOIN users ON comments.user_id = users.id WHERE comments.post_id = {$row['id']}");
                    while ($comment = $comments->fetch_assoc()):
                    ?>
                        <p><strong><?php echo htmlspecialchars($comment['username']); ?>:</strong> <?php echo htmlspecialchars($comment['content']); ?></p>
                    <?php endwhile; ?>
                </div>

                <!-- Add some gap between comments and delete button -->
                <div style="margin-top: 10px;">
                    <!-- Show delete option only if the post belongs to the logged-in user -->
                    <?php if ($row['user_id'] == $user_id): ?>
                        <form method="POST" action="" style="display:inline;">
                            <input type="hidden" name="delete_post_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" onclick="return confirm('Are you sure you want to delete this post?');">Delete Post</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>

        <h3>Send Friend Request</h3>
        <form method="POST" action="">
            <select name="friend_id" required>
                <option value="">Select a friend</option>
                <?php while ($friend = $all_users->fetch_assoc()): ?>
                    <option value="<?php echo $friend['id']; ?>">
                        <?php echo htmlspecialchars($friend['name']); ?> 
                        (Genre: <?php echo htmlspecialchars($friend['genre']); ?>, Favorite Book: <?php echo htmlspecialchars($friend['favorite_book']); ?>)
                    </option>
                <?php endwhile; ?>
            </select>
            <button type="submit">Send Friend Request</button>
        </form>

        <h3>Incoming Friend Requests</h3>
        <div class="incoming-requests">
            <?php while ($request = $incoming_requests->fetch_assoc()): ?>
                <p>
                    <?php echo htmlspecialchars($request['sender_name']); ?> 
                    <strong>Genre:</strong> <?php echo htmlspecialchars($request['genre']); ?>, 
                    <strong>Favorite Book:</strong> <?php echo htmlspecialchars($request['favorite_book']); ?>
                    <form method="POST" action="" style="display:inline;">
                        <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                        <button type="submit">Accept</button>
                    </form>
                </p>
            <?php endwhile; ?>
        </div>

        <h3>Message Your Friends</h3>
        <form method="POST" action="">
            <select name="receiver_id" required>
                <option value="">Select a friend</option>
                <?php while ($friend = $friends->fetch_assoc()): ?>
                    <option value="<?php echo $friend['id']; ?>">
                        <?php echo htmlspecialchars($friend['name']); ?> 
                        (Genre: <?php echo htmlspecialchars($friend['genre']); ?>, Favorite Book: <?php echo htmlspecialchars($friend['favorite_book']); ?>)
                    </option>
                <?php endwhile; ?>
            </select>
            <textarea name="message_content" placeholder="Type your message here..." required></textarea>
            <button type="submit">Send Message</button>
        </form>

        <h3>Your Messages</h3>
        <div class="messages">
            <?php while ($message = $messages->fetch_assoc()): ?>
                <p><strong><?php echo htmlspecialchars($message['sender_name']); ?>:</strong> <?php echo htmlspecialchars($message['content']); ?> <em>(<?php echo $message['created_at']; ?>)</em></p>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>
