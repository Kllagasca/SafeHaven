<?php
require_once 'config/function.php';
include 'config/supabase_connect.php';
$pageTitle = "Login";
 alertmessage();
try {
    // Establish a PDO connection
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}

// Redirect if the user is already logged in
if (isset($_SESSION['auth'])) {
    redirect('index.php', 'You are already logged in');
}

// Handle form submissions


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['password']);
    
    if ($_POST['action'] === 'login') {
        // Handle Login
        if ($email && $password) {
            $query = "SELECT * FROM users WHERE email = :email LIMIT 1";
            $stmt = $pdo->prepare($query);
            $stmt->execute(['email' => $email]);

            if ($stmt->rowCount() === 1) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if (password_verify($password, $row['password'])) {
                    if ($row['is_ban'] == 1) {
                        redirect('login.php', 'Your account has been banned. Please contact admin.');
                    }

                    $_SESSION['auth'] = true;
                    $_SESSION['fname'] = $row['fname'];
                    $_SESSION['lname'] = $row['lname'];
                    $_SESSION['email'] = $row['email'];
                    $_SESSION['role'] = $row['role'];

                    // Redirect based on role
                    $redirectUrl = match ($row['role']) {
                        'admin' => 'admin/index.php',
                        'fperson' => 'focal-person/index.php',
                        'researcher' => 'researcher/index.php',
                        default => 'index.php',
                    };

                    redirect($redirectUrl, 'Logged in Successfully');
                } else {
                    redirect('login.php', 'Invalid Email or Password.');
                }
            } else {
                redirect('login.php', 'Invalid Email or Password.');
            }
        } else {
            redirect('login.php', 'All fields are required.');
        }
    } elseif ($_POST['action'] === 'register') {
        // Handle Registration
        $fname = trim($_POST['fname']);
        $lname = trim($_POST['lname']);
        $role = trim($_POST['role']);

        if (!empty($fname) && !empty($lname) && !empty($email) && !empty($password) && !empty($role)) {
            if (!in_array($role, ['fperson', 'researcher', 'admin'])) { // Include 'admin' if applicable
                redirect('login.php', 'Invalid role selected.');
            }

            // Check if email is already registered
            $check_email_query = "SELECT * FROM users WHERE email = :email";
            $stmt = $pdo->prepare($check_email_query);
            $stmt->execute(['email' => $email]);

            if ($stmt->rowCount() > 0) {
                redirect('login.php', 'Email is already registered.');
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert user into database
                $insert_user_query = "INSERT INTO users (fname, lname, email, password, role) VALUES (:fname, :lname, :email, :password, :role)";
                $stmt = $pdo->prepare($insert_user_query);
                $stmt->execute([
                    'fname' => $fname,
                    'lname' => $lname,
                    'email' => $email,
                    'password' => $hashed_password,
                    'role' => $role,
                ]);

                // Add a registration notification for the admin
                $userId = $pdo->lastInsertId(); // Get the ID of the newly created user
                $event = 'registration';
                $message = "New user {$fname} {$lname} registered as {$role}.";

                // Updated query to match the parameters being passed
                $insert_notification_query = "INSERT INTO notifications (user_id, event, message) VALUES (:user_id, :event, :message)";
                $notification_stmt = $pdo->prepare($insert_notification_query);
                $notification_stmt->execute([
                    'user_id' => $userId,
                    'event' => $event,
                    'message' => $message,
                ]);

                redirect('login.php', 'Registration successful! Please log in.');
            }
        } else {
            redirect('login.php', 'All fields are required.');
        }
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In/Up Form</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,800" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        /* CSS styles */
        * {
            box-sizing: border-box;
        }

        body {
            background: url('assets/img/bg3.png') no-repeat center center / cover;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            font-family: 'Montserrat', sans-serif;
            height: 90vh;
            margin: -20px 0 50px;
        }

        h1 {
            font-weight: bold;
            margin: 0;
        }

        h2 {
            text-align: center;
        }

        p {
            font-size: 14px;
            font-weight: 100;
            line-height: 20px;
            letter-spacing: 0.5px;
            margin: 10px 0 10px;
        }

        span {
            font-size: 12px;
        }

        a {
            color: #333;
            font-size: 14px;
            text-decoration: none;
            margin: 15px 0;
        }

        button {
            border-radius: 20px;
            border: 1px solid white;
            background-color: #9953ed;
            color: #FFFFFF;
            font-size: 12px;
            font-weight: bold;
            padding: 12px 45px;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: transform 80ms ease-in;
            margin-top: 5px;
        }

        button:active {
            transform: scale(0.95);
        }

        button:focus {
            outline: none;
        }

        button.ghost {
            background-color: transparent;
            border-color: #FFFFFF;
        }

        form {
            background-color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 0 50px;
            height: 100%;
            text-align: center;
        }

        input {
            background-color: #eee;
            border: none;
            padding: 12px 15px;
            margin: 8px 0;
            width: 100%;
        }

        .container {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 14px 28px rgba(0,0,0,0.25), 
                        0 10px 10px rgba(0,0,0,0.22);
            position: relative;
            overflow: hidden;
            width: 768px;
            max-width: 100%;
            min-height: 480px;
            margin-top: 100px;
        }

        .form-container {
            position: absolute;
            top: 0;
            height: 100%;
            transition: all 0.6s ease-in-out;
        }

        .sign-in-container {
            left: 0;
            width: 50%;
            z-index: 2;
        }

        .container.right-panel-active .sign-in-container {
            transform: translateX(100%);
        }

        .sign-up-container {
            left: 0;
            width: 50%;
            opacity: 0;
            z-index: 1;
        }

        .container.right-panel-active .sign-up-container {
            transform: translateX(100%);
            opacity: 1;
            z-index: 5;
            animation: show 0.6s;
        }

        @keyframes show {
            0%, 49.99% {
                opacity: 0;
                z-index: 1;
            }
            
            50%, 100% {
                opacity: 1;
                z-index: 5;
            }
        }

        .overlay-container {
            position: absolute;
            top: 0;
            left: 50%;
            width: 50%;
            height: 100%;
            overflow: hidden;
            transition: transform 0.6s ease-in-out;
            z-index: 100;
        }

        .container.right-panel-active .overlay-container {
            transform: translateX(-100%);
        }

        .overlay {
            background: #FF416C;
            background: linear-gradient(to right, #ff4de2, #9953ed);
            background-repeat: no-repeat;
            background-size: cover;
            background-position: 0 0;
            color: #FFFFFF;
            position: relative;
            left: -100%;
            height: 100%;
            width: 200%;
            transform: translateX(0);
            transition: transform 0.6s ease-in-out;
        }

        .container.right-panel-active .overlay {
            transform: translateX(50%);
        }

        .overlay-panel {
            position: absolute;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 0 40px;
            text-align: center;
            top: 0;
            height: 100%;
            width: 50%;
            transform: translateX(0);
            transition: transform 0.6s ease-in-out;
        }

        .overlay-left {
            transform: translateX(-20%);
        }

        .container.right-panel-active .overlay-left {
            transform: translateX(0);
        }

        .overlay-right {
            right: 0;
            transform: translateX(0);
        }

        .container.right-panel-active .overlay-right {
            transform: translateX(20%);
        }

        .social-container {
            margin: 20px 0;
        }

        .social-container a {
            border: 1px solid #DDDDDD;
            border-radius: 50%;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            margin: 0 5px;
            height: 40px;
            width: 40px;
        }

        footer {
            background-color: #222;
            color: #fff;
            font-size: 14px;
            bottom: 0;
            position: fixed;
            left: 0;
            right: 0;
            text-align: center;
            z-index: 999;
        }

        footer p {
            margin: 10px 0;
        }

        footer i {
            color: red;
        }

        footer a {
            color: #3c97bf;
            text-decoration: none;
        }
    .custom-select {
        position: relative;
        display: inline-block;
        width: 100%;
        max-width: 300px;
    }

    .custom-select-button {
        background: pink;
        color: white;
        padding: 10px 20px;
        font-size: 16px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        width: 100%;
        text-align: left;
        margin-bottom: 10px;
        margin-top: 5px;
    }

    .custom-select-button:hover {
        background: linear-gradient(to right, #9953ed, #ff4de2);
    }

    .custom-select-options {
        display: none;
        position: absolute;
        background-color: white;
        border: 1px solid #ccc;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        z-index: 10;
        width: 100%;
        border-radius: 5px;
    }

    .custom-select-options button {
        padding: 10px;
        width: 100%;
        border: none;
        background: none;
        text-align: left;
        cursor: pointer;
        color: black;
    }

    .custom-select-options button:hover {
        background-color: #f1f1f1;
    }

    .custom-select.active .custom-select-options {
        display: block;
    }
    </style>
</head>
<body>

    <div class="container" id="container">
        <div class="form-container sign-up-container">
            <form method="POST" action="login.php">
                <h1>Create Account</h1>
                <input type="text" name="fname" placeholder="First Name" required />
                <input type="text" name="lname" placeholder="Last Name" required />
                <input type="email" name="email" placeholder="Email" required />
                <input type="password" name="password" placeholder="Password" required />
                <div class="custom-select">
                    <button type="button" class="custom-select-button" id="roleButton">-- Select Role --</button>
                    <div class="custom-select-options">
                        <button type="button" data-value="fperson">Focal Person</button>
                        <button type="button" data-value="researcher">Researcher</button>
                    </div>
                    <input type="hidden" name="role" id="roleInput" required>
                </div>
                <button type="submit" name="action" value="register">Sign Up</button>
            </form>
        </div>
        <div class="form-container sign-in-container">
        <div style="position: absolute; top: 20px; left: 20px; z-index: 1000;">
        <a href="index.php" style="text-decoration: none; color: #9953ed; font-weight: bold;">
            <i class="fas fa-arrow-left" style="margin-right: 5px;"></i> Back Home
        </a>
    </div>
            <form method="POST" action="login.php">
                <h1>Sign In</h1>
                <div class="social-container">
                    <a href="#" class="social"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social"><i class="fab fa-google-plus-g"></i></a>
                    <a href="#" class="social"><i class="fab fa-linkedin-in"></i></a>
                </div>

                <input type="email" name="email" placeholder="Email" required />
                <input type="password" name="password" placeholder="Password" required />
                <button type="submit" name="action" value="login">Sign In</button>
                <a href="forgot-password.php">Forgot password?</a>
            </form>
        </div>
        <div class="overlay-container">
            <div class="overlay">
                <div class="overlay-panel overlay-left">
                    <h3>SafeHaven Online Platform</h3>
                    <img src="assets/img/logo.png" alt="Logo" style="width: 300px;">
                    <p>Already have an account?</p>
                    <button class="ghost" id="signIn">Sign In</button>
                </div>
                <div class="overlay-panel overlay-right">
                    <h3>SafeHaven Online Platform</h3>
                    <img src="assets/img/logo.png" alt="Logo" style="width: 300px;">
                    <p>Don't have an account?</p>
                    <button class="ghost" id="signUp">Sign Up</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const signUpButton = document.getElementById('signUp');
        const signInButton = document.getElementById('signIn');
        const container = document.getElementById('container');

        signUpButton.addEventListener('click', () => {
            container.classList.add("right-panel-active");
        });

        signInButton.addEventListener('click', () => {
            container.classList.remove("right-panel-active");
        });
    </script>

<script>
    const selectButton = document.getElementById('roleButton');
    const selectOptions = document.querySelector('.custom-select-options');
    const roleInput = document.getElementById('roleInput');

    // Toggle options display on button click
    selectButton.addEventListener('click', () => {
        selectOptions.parentElement.classList.toggle('active');
    });

    // Handle option selection
    selectOptions.addEventListener('click', (e) => {
        if (e.target.tagName === 'BUTTON') {
            const selectedValue = e.target.getAttribute('data-value');
            const selectedText = e.target.innerText;

            roleInput.value = selectedValue; // Set the hidden input's value
            selectButton.innerText = selectedText; // Update button text
            selectOptions.parentElement.classList.remove('active'); // Hide options
        }
    });

    // Close the dropdown if clicked outside
    document.addEventListener('click', (e) => {
        if (!selectButton.contains(e.target) && !selectOptions.contains(e.target)) {
            selectOptions.parentElement.classList.remove('active');
        }
    });
</script>
</body>
</html>
