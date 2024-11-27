<?php
session_start();

// Database Configuration and Connection
$host = 'localhost'; // Database host
$db_name = 'financial_management'; // Database name
$username = 'root'; // Database username (default for XAMPP)
$password = ''; // Database password (default is empty for XAMPP)

try {
    // Create a new PDO instance
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);

    // Set PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Log out functionality
if (isset($_POST['logout'])) {
    // Destroy session and redirect to login page
    session_destroy();
    header('Location: login.php');
    exit;
}

// Fetch user details
$sql = "SELECT * FROM users WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle profile and password updates (optional functionality can be added here)
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .settings-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #ccc;
            padding: 10px;
        }
        .settings-icon {
            cursor: pointer;
            font-size: 24px;
        }
        .settings-menu {
            display: flex;
            flex-direction: column;
            padding: 10px;
        }
        .settings-section {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="settings-header">
        <h1>Settings</h1>
        <div class="settings-icon">⚙️</div>
    </div>

    <!-- Settings Menu -->
    <div class="settings-menu">
        <a href="#edit-profile">Edit Profile</a>
        <a href="#notifications">Notifications</a>
        <a href="#security-privacy">Security & Privacy</a>
        <a href="#language">Language</a>
        <a href="#terms-conditions">Terms & Conditions</a>
        <a href="#help-center">Help Center</a>
        <form method="POST" style="margin-top: 20px;">
            <button type="submit" name="logout">Log Out</button>
        </form>
    </div>

    <!-- Edit Profile Section -->
    <div id="edit-profile" class="settings-section">
        <h2>Edit Profile</h2>
        <p>Username: <?= htmlspecialchars($user['username']) ?></p>
        <p>Email: <?= htmlspecialchars($user['email']) ?></p>
        <!-- Add a form here for editing profile if needed -->
    </div>

    <!-- Notifications Section -->
    <div id="notifications" class="settings-section">
        <h2>Notifications</h2>
        <p>No new notifications.</p>
    </div>

    <!-- Security & Privacy Section -->
    <div id="security-privacy" class="settings-section">
        <h2>Security & Privacy</h2>
        <p>Change password and manage your account's security.</p>
    </div>

    <!-- Language Selector Section -->
    <div id="language" class="settings-section">
        <h2>Language</h2>
        <select>
            <option value="en" selected>English</option>
            <option value="fr">French</option>
            <option value="es">Spanish</option>
        </select>
    </div>

    <!-- Terms & Conditions Section -->
    <div id="terms-conditions" class="settings-section">
        <h2>Terms & Conditions</h2>
        <p>Read our <a href="terms.php">Terms and Conditions</a>.</p>
    </div>

    <!-- Help Center Section -->
    <div id="help-center" class="settings-section">
        <h2>Help Center</h2>
        <p>Need assistance? Visit the <a href="help.php">Help Center</a>.</p>
    </div>
</body>
</html>
