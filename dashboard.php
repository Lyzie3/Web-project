<?php
session_start();

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

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch user details
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Fetch total expenses
$sql_expenses = "SELECT SUM(amount) AS total_expenses FROM expense WHERE user_id = :user_id";
$stmt_expenses = $conn->prepare($sql_expenses);
$stmt_expenses->execute([':user_id' => $user_id]);
$total_expenses = $stmt_expenses->fetch(PDO::FETCH_ASSOC)['total_expenses'];

// Fetch total budget
$sql_budget = "SELECT SUM(amount) AS total_budget FROM budget WHERE user_id = :user_id";
$stmt_budget = $conn->prepare($sql_budget);
$stmt_budget->execute([':user_id' => $user_id]);
$total_budget = $stmt_budget->fetch(PDO::FETCH_ASSOC)['total_budget'];

// Get current month
$current_month = date('F Y');

// Fetch budget categories and amounts
$sql_budget_categories = "SELECT category, SUM(amount) AS budgeted_amount FROM budget WHERE user_id = :user_id GROUP BY category";
$stmt_budget_categories = $conn->prepare($sql_budget_categories);
$stmt_budget_categories->execute([':user_id' => $user_id]);
$budget_categories = $stmt_budget_categories->fetchAll(PDO::FETCH_ASSOC);

// Fetch expense categories and amounts
$sql_expense_categories = "SELECT category, SUM(amount) AS spent_amount FROM expense WHERE user_id = :user_id GROUP BY category";
$stmt_expense_categories = $conn->prepare($sql_expense_categories);
$stmt_expense_categories->execute([':user_id' => $user_id]);
$expense_categories = $stmt_expense_categories->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
< lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            display: flex;
            flex-direction: column; /* Make body a flex container */
            min-height: 100vh; /* Ensure full viewport height is occupied */
        }


    /* Sidebar Styles (unchanged) */
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

        /* Centering the Dashboard heading */
        h1 {
            text-align: center;
            margin-bottom: 30px;
        }
        p2{
            text-align: center;
            color:#4B8E8D;
        }

        /* Container Styling for Total Expense, Total Budget, and Month */
        .container-cards {
            display: flex;
            justify-content: space-around;
            margin-bottom: 40px;
        }

        .card {
            background-color: #E8F5F4; /* Light teal to match the main theme */
            color: #FF8559; /* Retain the contrasting text color */
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 30%;
            padding: 20px;
            text-align: center;
}

        .card h4 {
            margin-bottom: 10px;
        }

        .card p {
            font-size: 1.2em;
        }
    </style>
</head>
<body>
    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <div class="logo">
            <i class="fas fa-chart-pie"></i>
        </div>
        <a href="my_profile.php"><i class="fas fa-user"></i><span>Profile</span></a>
        <a href="budget.php"><i class="fas fa-wallet"></i><span>Budget</span></a>
        <a href="expense.php"><i class="fas fa-shopping-cart"></i><span>Expense</span></a>
        <a href="settings.php"><i class="fas fa-cog"></i><span>Settings</span></a>
        <a href="notifications.php"><i class="fas fa-bell"></i><span>Notifications</span></a>
        <button class="logout-btn" onclick="location.href='log_out.php'"><i class="fas fa-sign-out-alt"></i><span>Logout</span></button>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h1>Dashboard</h1>
        <p2>This website/app is designed to help university students keep track of their expenses, set budgets, and visualize their financial data.through graphs. It also aims to </p2>
        <p2>address key financial challenges faced by university students, such as: poor budgeting, overspending, and financial stress. Take advantage of this site responsively.</p2>
        <p2>Remember, managing your finances is a journey and your first step starts here. Below is the breakdown of your monthly budget and expenses for each category.</p2><br><br>

        <!-- Containers for Total Expense, Total Budget, and Month -->
        <div class="container-cards">
            <div class="card">
                <h4>Total Budget</h4>
                <p>UGX<?php echo number_format($total_budget, 2); ?></p>
            </div>
            <div class="card">
                <h4>Total Expenses</h4>
                <p>UGX<?php echo number_format($total_expenses, 2); ?></p>
            </div>
            <div class="card">
                <h4>Month</h4>
                <p><?php echo $current_month; ?></p>
            </div>
        </div>

        
        <h2> Budget vs Expense by ategory </h2>
        <div style="width: 50%; float: left;">
            <canvas id="budgetChart"></canvas>
        </div>
        <div style="width: 50%; float: left;">
            <canvas id="expenseChart"></canvas>
        </div>
    </div>

    <script>
        // Data for Budget Chart
        const budgetData = {
            labels: <?php echo json_encode(array_column($budget_categories, 'category')); ?>,
            datasets: [{
                label: 'Budgeted Amount',
                data: <?php echo json_encode(array_column($budget_categories, 'budgeted_amount')); ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        };

        // Data for Expense Chart
        const expenseData = {
            labels: <?php echo json_encode(array_column($expense_categories, 'category')); ?>,
            datasets: [{
                label: 'Spent Amount',
                data: <?php echo json_encode(array_column($expense_categories, 'spent_amount')); ?>,
                backgroundColor: 'rgba(153, 102, 255, 0.2)',
                borderColor: 'rgba(153, 102, 255, 1)',
                borderWidth: 1
            }]
        };

        // Budget Chart
        const budgetCtx = document.getElementById('budgetChart').getContext('2d');
        new Chart(budgetCtx, {
            type: 'bar',
            data: budgetData,
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Budgeted Amount by Category'
                    },
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Expense Chart
        const expenseCtx = document.getElementById('expenseChart').getContext('2d');
        new Chart(expenseCtx, {
            type: 'bar',
            data: expenseData,
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Spent Amount by Category'
                    },
                    legend: {
                        display: false
                    }
                }
            }
        });
    </script>
    <!-- Existing body content -->

<!-- Footer -->
<footer class="footer bg-light text-center text-lg-start">
    <div class="container p-4">
        <!-- Social Media Section -->
        <section class="mb-4">
            <a href="#" class="btn btn-primary btn-floating m-1" style="background-color: #3b5998;" role="button"><i class="fab fa-facebook-f"></i></a>
            <a href="#" class="btn btn-primary btn-floating m-1" style="background-color: #00aced;" role="button"><i class="fab fa-twitter"></i></a>
            <a href="#" class="btn btn-primary btn-floating m-1" style="background-color: #dd4b39;" role="button"><i class="fab fa-google"></i></a>
            <a href="#" class="btn btn-primary btn-floating m-1" style="background-color: #ac2bac;" role="button"><i class="fab fa-instagram"></i></a>
            <a href="#" class="btn btn-primary btn-floating m-1" style="background-color: #0082ca;" role="button"><i class="fab fa-linkedin-in"></i></a>
            <a href="#" class="btn btn-primary btn-floating m-1" style="background-color: #333333;" role="button"><i class="fab fa-github"></i></a>
        </section>

        <!-- About Section -->
        <section class="mb-4">
            <p>
                Financial Management is designed to empower university students to manage their expenses and set financial goals effectively. 
                Gain control over your finances with insightful graphs and reports.
            </p>
        </section>

        <!-- Links Section -->
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
                <h5 class="text-uppercase">Quick Links</h5>
                <ul class="list-unstyled mb-0">
                    <li><a href="dashboard.php" class="text-dark">Dashboard</a></li>
                    <li><a href="budget.php" class="text-dark">Budget</a></li>
                    <li><a href="expense.php" class="text-dark">Expenses</a></li>
                    <li><a href="settings.php" class="text-dark">Settings</a></li>
                </ul>
            </div>
            <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
                <h5 class="text-uppercase">Support</h5>
                <ul class="list-unstyled mb-0">
                    <li><a href="#" class="text-dark">FAQ</a></li>
                    <li><a href="#" class="text-dark">Contact Us</a></li>
                    <li><a href="#" class="text-dark">Privacy Policy</a></li>
                    <li><a href="#" class="text-dark">Terms of Service</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Footer Bottom -->
    <div class="text-center p-3" style="background-color: #f8f9fa; color: #4B8E8D;">
        © 2024 Financial Management | Designed with ❤ for Students
    </div>
</footer>


</body>
</html>
