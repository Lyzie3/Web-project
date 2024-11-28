<?php
session_start();

// Database connection
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

$user_id = $_SESSION['user_id'];

// Handle search query
$search_query = '';
if (isset($_POST['search'])) {
    $search_query = $_POST['search_query'];
}

// Fetch expenses with search functionality
try {
    if ($search_query) {
        $stmt = $conn->prepare("SELECT * FROM expense WHERE user_id = :user_id AND (category LIKE :search_query OR description LIKE :search_query OR date LIKE :search_query)");
        $stmt->execute([':user_id' => $user_id, ':search_query' => "%$search_query%"]);
    } else {
        $stmt = $conn->prepare("SELECT * FROM expense WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $user_id]);
    }
    $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching expenses: " . $e->getMessage());
}

// Fetch category-specific budgets
try {
    $budget_stmt = $conn->prepare("SELECT category, amount FROM budget WHERE user_id = :user_id");
    $budget_stmt->execute([':user_id' => $user_id]);
    $budgets = $budget_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Create a map for quick lookup of budgets
    $category_budgets = [];
    foreach ($budgets as $budget) {
        $category_budgets[$budget['category']] = $budget['amount'];
    }
} catch (PDOException $e) {
    die("Error fetching budgets: " . $e->getMessage());
}

// Handle Add, Edit, and Delete operations
$error_message = '';

if (isset($_POST['add_expense'])) {
    $category = $_POST['category'];
    $description = $_POST['description'];
    $amount = $_POST['amount'];
    $date = $_POST['date'];

    // Calculate existing expenses in the category
    try {
        $expense_stmt = $conn->prepare("SELECT SUM(amount) AS total_expense FROM expense WHERE user_id = :user_id AND category = :category");
        $expense_stmt->execute([':user_id' => $user_id, ':category' => $category]);
        $total_expense = $expense_stmt->fetch(PDO::FETCH_ASSOC)['total_expense'] ?? 0;
    } catch (PDOException $e) {
        die("Error fetching existing expenses: " . $e->getMessage());
    }

    // Check if the new expense exceeds the budget
    $budget_limit = $category_budgets[$category] ?? 0; // Default to 0 if no budget set for the category
    if ($total_expense + $amount > $budget_limit) {
        $error_message = "Cannot add this expense. It exceeds the budget limit for '$category' (UGX " . number_format($budget_limit) . ").";
    } else {
        // Proceed to add the expense
        try {
            $stmt = $conn->prepare("INSERT INTO expense (user_id, category, description, amount, date) VALUES (:user_id, :category, :description, :amount, :date)");
            $stmt->execute([
                ':user_id' => $user_id,
                ':category' => $category,
                ':description' => $description,
                ':amount' => $amount,
                ':date' => $date
            ]);
            header('Location: expense.php');
            exit;
        } catch (PDOException $e) {
            die("Error adding expense: " . $e->getMessage());
        }
    }
}

// Handle Delete Expense
if (isset($_POST['delete_expense'])) {
    $expense_id = $_POST['expense_id'];

    try {
        $stmt = $conn->prepare("DELETE FROM expense WHERE id = :expense_id AND user_id = :user_id");
        $stmt->execute([':expense_id' => $expense_id, ':user_id' => $user_id]);
        header('Location: expense.php');
        exit;
    } catch (PDOException $e) {
        die("Error deleting expense: " . $e->getMessage());
    }
}

// Include TCPDF Library
require_once __DIR__ . '/tcpdf/tcpdf.php';

// Handle PDF export
if (isset($_POST['export_pdf'])) {
    exportPDF($expenses);
}

// Function to export expenses as a PDF
function exportPDF($expenses) {
    $pdf = new TCPDF();

    // Set document information
    $pdf->SetCreator('TCPDF');
    $pdf->SetAuthor('Financial Management');
    $pdf->SetTitle('Expenses Report');
    $pdf->SetSubject('Expenses Report');

    // Add a page
    $pdf->AddPage();

    // Prepare HTML content for the PDF
    $html = '<h1 style="text-align: center;">Expenses Report</h1>';
    $html .= '<table border="1" cellspacing="0" cellpadding="5" width="100%">';
    $html .= '<thead>';
    $html .= '<tr>';
    $html .= '<th>Category</th>';
    $html .= '<th>Description</th>';
    $html .= '<th>Amount (UGX)</th>';
    $html .= '<th>Date</th>';
    $html .= '</tr>';
    $html .= '</thead>';
    $html .= '<tbody>';

    foreach ($expenses as $expense) {
        $html .= '<tr>';
        $html .= '<td>' . htmlspecialchars($expense['category']) . '</td>';
        $html .= '<td>' . htmlspecialchars($expense['description']) . '</td>';
        $html .= '<td>' . number_format(htmlspecialchars($expense['amount'])) . '</td>';
        $html .= '<td>' . htmlspecialchars($expense['date']) . '</td>';
        $html .= '</tr>';
    }

    $html .= '</tbody>';
    $html .= '</table>';

    // Write HTML to the PDF
    $pdf->writeHTML($html, true, false, true, false, '');

    // Output PDF for download
    $pdf->Output('expenses_report.pdf', 'D');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Expenses</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* Navigation Bar */
        .navbar {
            background-color: #4b8e8d;
        }
        .navbar-brand, .nav-link {
            color: white !important;
        }
        .nav-link.active {
            font-weight: bold;
            text-decoration: underline;
        }

        /* Buttons */
        .btn-primary {
            background-color: #4b8e8d;
            border-color: #4b8e8d;
        }
        .btn-primary:hover {
            background-color: #3d7474;
            border-color: #3d7474;
        }
        .btn-danger {
            background-color: #c44b4b;
            border-color: #c44b4b;
        }
        .btn-danger:hover {
            background-color: #a63b3b;
            border-color: #a63b3b;
        }

        /* Table */
        table thead {
            background-color: #4b8e8d;
            color: white;
        }
        table tbody tr:hover {
            background-color: #e8f8f8;
        }

        /* Headers */
        h1, h2 {
            color: #4b8e8d;
        }

        /* Image Style */
        .banner-img {
            width: 89%;
            height: 400px;
            margin-top: 20px;
            border-radius: 10px;
        }

        /* Custom Styles for the Search Bar */
        .input-group {
            border-radius: 25px;
            border: 1px solid #ddd;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .input-group input.form-control {
            border-top-left-radius: 25px;
            border-bottom-left-radius: 25px;
            padding: 20px 15px;
        }

        .input-group button {
            border-top-right-radius: 25px;
            border-bottom-right-radius: 25px;
            background-color: #4b8e8d;
            color: white;
            padding: 20px 30px;
        }

        .input-group button:hover {
            background-color: #3d7474;
            border-color: #3d7474;
        }

        .input-group button i {
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">MegaCash</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="my_profile.php">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="expense.php">EXPENSE</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="budget.php">Budget</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="settings.php">Settings</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="notifications.php">Notifications</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Banner Image -->
    <img src="gold.jpg" alt="Banner Image" class="banner-img">

    <?php if (!empty($error_message)): ?>
    <div class="alert alert-danger" role="alert" id="error-message" style="display: none;">
        <?= htmlspecialchars($error_message) ?>
    </div>
    <script>
        // Show the error message with a fade-in effect
        var errorMessage = document.getElementById('error-message');
        errorMessage.style.display = 'block';
        errorMessage.style.opacity = 0;
        errorMessage.style.transition = 'opacity 1s ease-in-out';

        setTimeout(function() {
            errorMessage.style.opacity = 1;  // Fade in effect

            setTimeout(function() {
                errorMessage.style.opacity = 0; // Fade out effect
            }, 8000);  // Wait for 8 seconds before starting the fade-out

        }, 100); // Delay the fade-in effect slightly for a smoother transition

        // Hide the error message after 15 seconds
        setTimeout(function() {
            errorMessage.style.display = 'none';
        }, 10000); // 10000ms = 15 seconds
    </script>
<?php endif; ?>


    <!-- Main Content -->
    <div class="container mt-5">
        <h1>Track Expenses</h1>
        <p>You can know how you are spending your money on different categories of expenses and on which date. You can also export these details in the form of a PDF for personal use.</p>
        
        <!-- Add Expense Form -->
        <form method="POST" action="">
            <div class="mb-3">
                <label for="category" class="form-label">Category:</label>
                <select name="category" id="category" class="form-select" required>
                    <option value="Housing">Housing</option>
                    <option value="Food">Food</option>
                    <option value="Transportation">Transportation</option>
                    <option value="Utilities">Utilities</option>
                    <option value="Entertainment">Entertainment</option>
                    <option value="Health">Health</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description:</label>
                <textarea name="description" id="description" class="form-control"></textarea>
            </div>
            <div class="mb-3">
                <label for="amount" class="form-label">Amount (UGX):</label>
                <input type="number" name="amount" id="amount" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="date" class="form-label">Date:</label>
                <input type="date" name="date" id="date" class="form-control" required>
            </div>
            <button type="submit" name="add_expense" class="btn btn-primary">Add Expense</button>
        </form>

        <hr>
        

        <!-- Search Bar Section -->
        <div class="row justify-content-center mb-4">
            <div class="col-md-4">
                <form method="POST" action="">
                    <div class="input-group shadow-sm">
                        <input type="text" name="search_query" class="form-control form-control-sm" placeholder="Search..." value="<?= htmlspecialchars($search_query) ?>" aria-label="Search expenses" aria-describedby="search-button">
                        <button type="submit" name="search" class="btn btn-outline-primary btn-sm" id="search-button">
                            <i class="bi bi-search"></i> Search
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Expenses Table -->
        <h2>Expenses</h2>
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Amount (UGX)</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($expenses as $expense): ?>
                    <tr>
                        <td><?= htmlspecialchars($expense['category']) ?></td>
                        <td><?= htmlspecialchars($expense['description']) ?></td>
                        <td><?= number_format(htmlspecialchars($expense['amount'])) ?></td>
                        <td><?= htmlspecialchars($expense['date']) ?></td>
                        <td>
                            <form method="POST" action="">
                                <input type="hidden" name="expense_id" value="<?= $expense['id'] ?>">
                                <button type="submit" name="delete_expense" class="btn btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Export PDF Button -->
        <form method="POST" action="">
            <button type="submit" name="export_pdf" class="btn btn-primary">Export to PDF</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
