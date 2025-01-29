<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $genre = $_POST['genre'];
    $favourite_book = $_POST['favourite_book'];
    
    $stmt = $mysqli->prepare("INSERT INTO users (name, username, email, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $username, $email, $password);
    
    if ($stmt->execute()) {
        header("Location: login.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup Page</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #ff6f61, #d83a5a);
            background-size: cover;
        }
        .signup-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.2);
            max-width: 360px;
            width: 90%; /* Adjusted width for smaller screens */
            animation: slideIn 0.8s ease-out;
        }
        .signup-container h2 {
            margin: 0 0 15px;
            font-size: 28px;
            color: #333;
            text-align: center;
        }
        .signup-container label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #555;
        }
        .signup-container input,
        .signup-container textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 14px;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        .signup-container input:focus,
        .signup-container textarea:focus {
            border-color: #d83a5a;
            box-shadow: 0 0 6px rgba(216, 58, 90, 0.2);
            outline: none;
        }
        .signup-container textarea {
            resize: none;
        }
        .signup-container button {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 6px;
            background: linear-gradient(135deg, #ff6f61, #d83a5a);
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s, transform 0.3s;
        }
        .signup-container button:hover {
            background: linear-gradient(135deg, #d83a5a, #ff6f61);
            transform: translateY(-2px);
        }
        .signup-container button:active {
            transform: translateY(1px);
        }
        .signup-container .error {
            color: #d83a5a;
            font-size: 12px;
            margin-top: -10px;
            margin-bottom: 10px;
        }
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @media (max-width: 400px) {
            .signup-container {
                padding: 20px;
                max-width: 95%;
            }
            .signup-container h2 {
                font-size: 24px;
            }
            .signup-container input,
            .signup-container textarea {
                font-size: 13px;
            }
            .signup-container button {
                font-size: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <h2>Sign Up</h2>
        <form id="signupForm" action="signup.php" method="POST">

            <div>
                <label for="name">Name</label>
                <input type="text" id="name" name="name" required>
                <span class="error" id="nameError"></span>
            </div>
            
            <div>
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
                <span class="error" id="emailError"></span>
            </div>

            <div>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
                <span class="error" id="passwordError"></span>
            </div>

            <div>
                <label for="confirmPassword">Confirm Password</label>
                <input type="password" id="confirmPassword" name="confirmPassword" required>
                <span class="error" id="confirmPasswordError"></span>
            </div>

            <div>
                <label for="username">Preferred Username</label>
                <input type="text" id="username" name="username" required>
                <span class="error" id="usernameError"></span>
            </div>
            
            <div>
                <label for="genre">Favorite Genre</label>
                <input type="text" id="genre" name="genre" placeholder="Enter your favorite genre(s)" required>
                <span class="error" id="genreError"></span>
            </div>
            
            <div>
                <label for="books">Favorite Book(s)</label>
                <textarea id="books" name="books" rows="2" placeholder="Enter your favorite book(s)" required></textarea>
                <span class="error" id="booksError"></span>
            </div>
            
            <button type="submit">Sign Up</button>
        </form>
    </div>

    <script>
        document.getElementById('signupForm').addEventListener('submit', function(event) {
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const username = document.getElementById('username').value.trim();
            const genre = document.getElementById('genre').value.trim();
            const books = document.getElementById('books').value.trim();

            let valid = true;

            // Reset errors
            document.getElementById('nameError').textContent = '';
            document.getElementById('emailError').textContent = '';
            document.getElementById('passwordError').textContent = '';
            document.getElementById('confirmPasswordError').textContent = '';
            document.getElementById('usernameError').textContent = '';
            document.getElementById('genreError').textContent = '';
            document.getElementById('booksError').textContent = '';

            if (name === '') {
                document.getElementById('nameError').textContent = 'Name is required.';
                valid = false;
            }

            if (email === '' || !/\S+@\S+\.\S+/.test(email)) {
                document.getElementById('emailError').textContent = 'Valid email is required.';
                valid = false;
            }

            if (password.length < 6) {
                document.getElementById('passwordError').textContent = 'Password must be at least 6 characters.';
                valid = false;
            }

            if (password !== confirmPassword) {
                document.getElementById('confirmPasswordError').textContent = 'Passwords do not match.';
                valid = false;
            }

            if (username === '') {
                document.getElementById('usernameError').textContent = 'Username is required.';
                valid = false;
            }

            if (genre === '') {
                document.getElementById('genreError').textContent = 'Favorite genre is required.';
                valid = false;
            }

            if (books === '') {
                document.getElementById('booksError').textContent = 'Favorite book(s) are required.';
                valid = false;
            }

            if (!valid) {
                event.preventDefault(); // Prevent form submission if not valid
            }
        });
    </script>
</body>
</html>
