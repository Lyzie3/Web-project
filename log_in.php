<?php
// Database Configuration and Connection
$host = 'localhost';
$db_name = 'financial_management';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$error_message = '';
session_start();

// CSRF Token Generation and Validation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF Token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('CSRF token validation failed.');
    }

    // Sanitize input
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    try {
        // Fetch user data from the database
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check user credentials
        if ($user && password_verify($password, $user['password'])) {
            // Start session and store user details
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];

            // Redirect to the dashboard
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header("Location: dashboard.php");
            exit();
        } else {
            $error_message = 'Invalid email or password.';
        }
    } catch (PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            color: #333;
            background-image: linear-gradient(to right, rgba(75, 142, 141, 0.8), rgba(153, 102, 255, 0.2)), url('budget_background.jpg');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh; /* Full viewport height */
            margin: 0;
        }

        form {
            width: 100%;
            max-width: 400px;
            background-color:rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.4);
            text-align: center;
        }

        h2 {
            color: #4B8E8D;
        }

        form input[type="email"],
        form input[type="password"],
        form button {
            width: 94%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
        }

        form button {
            background-color: #4B8E8D;
            color: #fff;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }

        form button:hover {
            background-color: #3A7470;
        }

        .toggle-password {
            display: inline-block;
            cursor: pointer;
            position: absolute;
            right: 20px;
            top: 55%;
            transform: translateY(-50%);
            color: #4B8E8D;
        }

        p.error {
            color: red;
            font-size: 0.9rem;
        }

        p {
            text-align: center;
        }

        p a {
            color: #4B8E8D;
            text-decoration: none;
            font-weight: bold;
        }

        p a:hover {
            text-decoration: underline;
        }

        .form-group {
            position: relative;
        }
    </style>
</head>
<body>
    <form action="log_in.php" method="POST">
        <h2>MegaCash Login</h2>
        <?php if ($error_message): ?>
            <p class="error"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required>
        </div>

        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>
            <span class="toggle-password" onclick="togglePasswordVisibility()">👁️</span>
        </div>

        <label>
            <input type="checkbox" name="remember_me"> Remember Me
        </label>

        <button type="submit">Login</button>
        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </form>

    <script>
        // Password Visibility Toggle
        function togglePasswordVisibility() {
            const passwordField = document.getElementById('password');
            passwordField.type = passwordField.type === 'password' ? 'text' : 'password';
        }
    </script>
</body>
</html>
