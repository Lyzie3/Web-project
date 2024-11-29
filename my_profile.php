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
$query = "SELECT first_name, last_name, gender, date_of_birth, year_of_study, program, profile_picture, username, email FROM Users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Fetch total budget and total expenses
$total_budget_query = "SELECT SUM(amount) AS total_budget FROM Budget WHERE user_id = ?";
$stmt = $conn->prepare($total_budget_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$total_budget = $result->fetch_assoc()['total_budget'];

$total_expense_query = "SELECT SUM(amount) AS total_expense FROM Expense WHERE user_id = ?";
$stmt = $conn->prepare($total_expense_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$total_expense = $result->fetch_assoc()['total_expense'];

// Handle profile picture update
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
                        // Update profile picture in the database
                        $query = "UPDATE Users SET profile_picture = ? WHERE id = ?";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("si", $target_file, $user_id);

                        if ($stmt->execute()) {
                            header("Location: my_profile.php");
                            exit();
                        } else {
                            $error = "Failed to update profile picture.";
                        }
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
}



// Fetch notifications for the user
$notifications = [];
$query = "SELECT message, created_at FROM Notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <!-- Google Fonts (Poppins) -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #e9f2f1;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            color: #343a40;
        }
        .navbar {
            background-color: #4b8e8d;
            border-bottom: 3px solid #3e7271;
            font-size:1.2rem;
        }
        
        .navbar .navbar-brand, .navbar .nav-link {
            color: #fff;
        }
        .navbar .nav-link:hover {
            background-color: #3e7271;
            border-radius: 5px;
        }
        .main-content {
            padding: 70px 50px;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            background: #ffffff;
        }
        .profile-img {
            width: 220px;
            height: 220px;
            object-fit: cover;
            border-radius: 50%;
            border: 5px solid #4b8e8d;
            transition: all 0.3s ease;
        }
        .profile-img:hover {
            transform: scale(1.1);
        }
        .stat-card {
            margin-bottom: 25px;
            padding: 25px;
            border-radius: 10px;
            color: #fff;
            font-weight: bold;
            min-height: 160px;
            text-align: center;
            transition: background 0.3s ease;
        }
        .main-content {
    padding: 40px 20px; /* Adjusted for better spacing */
}

.user-details-container {
    background-color: #ffffff;
    border-radius: 15px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    padding: 30px; /* Increased padding */
    text-align: center;
    margin-bottom: 30px; /* Space between containers */
}

.stat-section-container {
    background-color: #ffffff;
    border-radius: 15px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    padding: 30px;
}

.stat-card {
    min-height: 180px; /* Increased height */
    padding: 20px; /* Additional padding */
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.stat-card h6 {
    font-size: 1.4rem; /* Larger title */
}

.stat-card p {
    font-size: 2rem; /* Bigger numbers */
}

@media (max-width: 768px) {
    .user-details-container,
    .stat-section-container {
        padding: 20px;
    }

    .stat-card {
        min-height: 150px;
        padding: 15px;
    }

    .stat-card h6 {
        font-size: 1.2rem;
    }

    .stat-card p {
        font-size: 1.6rem;
    }
}
.stat-section-container {
    background-color: #ffffff;
    border-radius: 15px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    padding: 40px; /* Increased padding */
    text-align: center;
    margin-bottom: 30px;
}

.stat-card {
    min-height: 200px; /* Increased height */
    padding: 30px; /* Additional padding */
    display: flex;
    flex-direction: column;
    justify-content: center;
    border-radius: 12px;
    border: 3px solid rgba(0, 0, 0, 0.1); /* Subtle border */
    transition: all 0.3s ease-in-out;
}

.stat-card:hover {
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2); /* On hover */
    transform: scale(1.05); /* Slight zoom effect */
}

.stat-budget {
    background-color: #4b8e8d; /* Green for budget */
    color: #ffffff;
}

.stat-expense {
    background-color: rgba(153, 102, 255, 0.2); /* Red for remaining balance */
    color: #ffffff;
}

.stat-card h6 {
    font-size: 1.6rem; /* Larger titles */
    margin-bottom: 15px; /* Add spacing */
}

.stat-card p {
    font-size: 2.2rem; /* Larger numbers */
    font-weight: bold;
}

@media (max-width: 768px) {
    .stat-card {
        min-height: 150px;
        padding: 20px;
    }

    .stat-card h6 {
        font-size: 1.4rem;
    }

    .stat-card p {
        font-size: 1.8rem;
    }
}

    </style>
</head>
<body>
    <!-- Top Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">MegaCash</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="dashboard.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="budget.php">Budgets</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="expense.php">Expenses</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="settings.php">Settings</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="my_profile.php"><b>MY PROFILE</b></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="log out.php">Log Out</a>
                </li>
                <!-- Notifications Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-bell"></i> Notifications
                        <?php if (count($notifications) > 0): ?>
                            <span class="badge bg-danger"><?= count($notifications) ?></span>
                        <?php endif; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown" style="max-height: 300px; overflow-y: auto;">
                        <?php if (!empty($notifications)): ?>
                            <?php foreach ($notifications as $notification): ?>
                                <li>
                                    <a class="dropdown-item" href="#"><?= htmlspecialchars($notification['message']) ?>
                                        <small class="text-muted d-block"><?= date('F j, Y, g:i a', strtotime($notification['created_at'])) ?></small>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li><a class="dropdown-item text-muted" href="#">No new notifications</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

    <!-- Main Content -->
    <div class="main-content">
    <div class="container">
        <!-- User Details Section -->
        <div class="user-details-container mb-4">
            <img src="<?= !empty($user['profile_picture']) ? $user['profile_picture'] : 'https://via.placeholder.com/150' ?>" 
                 alt="Profile Picture" class="profile-img mb-3">
            <h5><?= htmlspecialchars($user['username']) ?></h5>
            <p class="text-muted"><?= htmlspecialchars($user['email']) ?></p>
            <p><strong>Full Name:</strong> <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></p>
            <p><strong>Gender:</strong> <?= htmlspecialchars($user['gender']) ?></p>
            <p><strong>Date of Birth:</strong> <?= date('F j, Y', strtotime($user['date_of_birth'])) ?></p>
            <p><strong>Year of Study:</strong> <?= htmlspecialchars($user['year_of_study']) ?></p>
            <p><strong>Program:</strong> <?= htmlspecialchars($user['program']) ?></p>
        </div>

        <!-- Stats Section -->
        <div class="stat-section-container">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="stat-card stat-budget">
                        <h6>Total Budget</h6>
                        <p class="h4"><?= $total_budget ? "UGX" . number_format($total_budget, 2) : "UGX0.00" ?></p>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="stat-card stat-expense">
                        <h6>Remaining Balance</h6>
                        <p class="h4">
                            <?= $total_budget - $total_expense > 0 ? "UGX" . number_format($total_budget - $total_expense, 2) : "UGX0.00" ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



            <!-- Profile Picture Update Section -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <h5>Update Profile Picture</h5>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="profile_picture" class="form-label">Profile Picture</label>
                            <input type="file" id="profile_picture" name="profile_picture" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary">Update Image</button><br><br>
                    </form>
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger mt-3"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 MegaCash. All rights reserved.</p>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
