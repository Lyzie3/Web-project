<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "financial_management");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$query = "SELECT * FROM Users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Fetch total spent and remaining balance
$query = "SELECT SUM(amount) as total_spent FROM Expense WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$expenses = $result->fetch_assoc();
$total_spent = $expenses['total_spent'] ?: 0;

// Assuming balance is stored in Users table
$total_expense = $user['total_expense'] ?: 0;
$remaining_balance = $total_expense - $total_spent;

// Handle profile update form submission
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars(trim($_POST['username']));
    $email = htmlspecialchars(trim($_POST['email']));
    $program = htmlspecialchars(trim($_POST['program']));
    $year_of_study = htmlspecialchars(trim($_POST['year_of_study']));

    $profile_picture_path = $user['profile_picture']; // Default to the existing picture

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true); // Ensure the directory exists
        }

        $target_file = $target_dir . basename($_FILES['profile_picture']['name']);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validate uploaded file
        if (getimagesize($_FILES['profile_picture']['tmp_name'])) {
            if ($_FILES['profile_picture']['size'] <= 5000000) {
                if (in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
                    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
                        $profile_picture_path = $target_file;
                    } else {
                        $error = "Failed to upload profile picture.";
                    }
                } else {
                    $error = "Only JPG, JPEG, PNG, and GIF files are allowed.";
                }
            } else {
                $error = "Profile picture size must be less than 5MB.";
            }
        } else {
            $error = "Uploaded file is not a valid image.";
        }
    }

    // Update user data if no errors
    if (empty($error)) {
        $query = "UPDATE Users SET username = ?, email = ?, program = ?, year_of_study = ?, profile_picture = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssi", $username, $email, $program, $year_of_study, $profile_picture_path, $user_id);

        if ($stmt->execute()) {
            header("Location: my_profile.php");
            exit();
        } else {
            $error = "Error updating profile.";
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #F8F9FA;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .container {
            max-width: 900px;
            margin-bottom: 50px; /* Add space for footer */
        }
        .card {
            border-radius: 12px;
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.1);
            background-color: #FFFFFF;
        }
        .profile-img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid #BC705B;
        }
        .form-control {
            border-radius: 8px;
        }
        .btn-custom {
            background-color: #BC705B;
            border-color: #BC705B;
        }
        .btn-custom:hover {
            background-color: #8B171B;
            border-color: #8B171B;
        }
        .navbar {
            margin-bottom: 30px;
        }
        .sidebar {
        width: 70px;
        height: 100vh;
        background-color: #4b8e8d;
        color: white;
        position: fixed;
        top: 0;
        left: 0;
        display: flex;
        flex-direction: column;
        transition: width 0.3s;
        overflow: hidden;
    }

    .sidebar:hover {
        width: 250px;
    }

    .main-content {
        margin-left: 70px;
        padding: 20px;
        width: calc(100% - 70px);
        transition: margin-left 0.3s;
        flex: 1; /* Ensures the main content expands to push footer down */
    }

    .sidebar:hover ~ .main-content {
        margin-left: 250px;
        width: calc(100% - 250px);
    }

    footer {
            background-color: #f8f9fa;
            color: #4B8E8D;
            padding: 15px;
            text-align: center;
            margin-top: 20px; /* Adds spacing above the footer */
        }    /* Sidebar Styles */
        .sidebar {
            width: 70px;
            height: 100vh;
            background-color: #4b8e8d;
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            display: flex;
            flex-direction: column;
            transition: width 0.3s;
            overflow: hidden;
        }

        .sidebar:hover {
            width: 250px;
        }

        .sidebar .logo {
            text-align: center;
            padding: 15px 0;
        }

        .sidebar a {
            padding: 15px 20px;
            text-decoration: none;
            color: white;
            display: flex;
            align-items: center;
            transition: 0.3s;
        }

        .sidebar a:hover {
            background-color: #3FB6B2;
        }

        .sidebar i {
            font-size: 18px;
            margin-right: 10px;
            min-width: 30px;
            text-align: center;
        }

        .sidebar span {
            opacity: 0;
            transition: opacity 0.3s;
        }

        .sidebar:hover span {
            opacity: 1;
        }

        .sidebar .logout-btn {
            background-color: red;
            color: white;
            text-align: center;
            padding: 15px;
            border: none;
            cursor: pointer;
            margin-top: auto;
        }

        .main-content {
            margin-left: 70px;
            padding: 20px;
            flex: 1; /* Allow main content to grow and push footer down */
        }


        .sidebar:hover ~ .main-content {
            margin-left: 250px;
            width: calc(100% - 250px);
        }

        .profile-content {
            margin-left: 270px;
        }
        .balance-info {
            background-color: #28A745;
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .profile-summary {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .profile-summary h3 {
            color: #BC705B;
        }
        .profile-summary p {
            color: #333;
        }
        /* Footer Styles */
        .footer {
            background-color: #BC705B;
            color: white;
            text-align: center;
            padding: 15px;
            position: relative;
            width: 100%;
            bottom: 0;
            margin-top: auto; /* Makes sure footer is always at the bottom */
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h4>Finance Dashboard</h4>
        <a href="dashboard.php">Dashboard</a>
        <a href="expense.php">Expenses</a>
        <a href="budget.php">Budget</a>
        <a href="my_profile.php">Profile</a>
        <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown" role="button" aria-expanded="false">Settings</a>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#changePasswordModal">Change Password</a></li>
            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#updatePreferencesModal">Update Preferences</a></li>
            <li><a class="dropdown-item" href="log_out.php">Logout</a></li>
        </ul>
    </div>
    <div class="profile-content container mt-5">
        <div class="card p-4">
            <!-- Profile Picture and Summary -->
            <div class="profile-summary mb-4">
                <img src="<?= !empty($user['profile_picture']) ? $user['profile_picture'] : 'https://via.placeholder.com/150' ?>" alt="Profile Picture" class="profile-img">
                <div>
                    <h3><?= htmlspecialchars($user['username']) ?></h3>
                    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                    <p><strong>Program:</strong> <?= htmlspecialchars($user['program']) ?></p>
                    <p><strong>Year of study:</strong> <?= htmlspecialchars($user['year_of_study']) ?></p>
                </div>
            </div>
            <!-- Balance Info -->
            <div class="balance-info">
                <h4>Total Spent: UGX<?= number_format($total_spent, 2) ?></h4>
                <h4>Remaining Balance: UGX<?= number_format($remaining_balance, 2) ?></h4>
            </div>
            <!-- Update Profile Form -->
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" id="username" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="program" class="form-label">Phone</label>
                    <input type="text" id="program" name="program" class="form-control" value="<?= htmlspecialchars($user['program']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="year_of_study" class="form-label">Address</label>
                    <input type="text" id="year_of_study" name="year_of_study" class="form-control" value="<?= htmlspecialchars($user['year_of_study']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="profile_picture" class="form-label">Profile Picture</label>
                    <input type="file" id="profile_picture" name="profile_picture" class="form-control">
                </div>
                <button type="submit" class="btn btn-custom">Save Change(s)</button>
            </form>
        </div>
    </div>
    <!-- Footer -->
    <div class="footer">
        <p>&copy; <?= date('Y') ?> Financial Management System. All Rights Reserved.</p>
    </div>
    <!-- Modal for Change Password -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changePasswordModalLabel">Change Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password">
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password">
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password">
                        </div>
                        <button type="submit" class="btn btn-custom">Change Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for Update Preferences -->
    <div class="modal fade" id="updatePreferencesModal" tabindex="-1" aria-labelledby="updatePreferencesModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updatePreferencesModalLabel">Update Preferences</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <!-- Add preferences form elements here -->
                        <button type="submit" class="btn btn-custom">Save Preferences</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>
