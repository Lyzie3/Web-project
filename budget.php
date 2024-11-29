<?php
// Database connection (replace with your actual database credentials)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "financial_management";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables for currency and budget
$currency = isset($_POST['currency']) ? $_POST['currency'] : 'USD';  // Default to USD
$currencies = [
    "USD" => "$",
    "EUR" => "€",
    "GBP" => "£",
    "UGX" => "UGX"
];

// Fetch budgets from the database
$budget = [];
$sql = "SELECT * FROM budget";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $budget[] = $row;
    }
}

// Insert budget data if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['category'])) {
    session_start();  // Ensure session is started
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

    if (!$user_id) {
        // If user_id is not available in session, redirect to login page
        header("Location: login.php");
        exit();
    }

    $category = $_POST['category'];
    $amount = $_POST['amount'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    // If 'Other' is selected, use custom category
    if ($category === 'Other') {
        $category = $_POST['custom_category'];
    }
    
    // Check if there's an overlapping budget for the same category and dates
    $sql_check_overlap = "
        SELECT * FROM budget
        WHERE user_id = ? AND category = ? 
        AND ((start_date BETWEEN ? AND ?) OR (end_date BETWEEN ? AND ?))
    ";
    $stmt_check = $conn->prepare($sql_check_overlap);
    $stmt_check->bind_param("isssss", $user_id, $category, $start_date, $end_date, $start_date, $end_date);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // Overlapping budget found, show error message
        $error_message = "You already have a budget for this category within the selected date range.";
    } else {
        // No overlap, proceed with inserting the new budget
        $sql_insert = "INSERT INTO budget (category, amount, start_date, end_date, user_id) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql_insert);
        $stmt->bind_param("sssss", $category, $amount, $start_date, $end_date, $user_id);
        $stmt->execute();
        $stmt->close();

        // After successfully updating a budget
        $query = "INSERT INTO Notifications (user_id, message, type, created_at) VALUES (?, ?, 'budget', NOW())";
        $message = "Your budget has been updated.";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("is", $user_id, $message);
        $stmt->execute();

        // Log the action in the user_logs table
$log_sql = "INSERT INTO user_logs (user_id, action_type, action_description) 
VALUES (?, 'update_profile', ?)";
$log_stmt = $conn->prepare($log_sql);

if ($log_stmt) {
$description = "Updated profile details (e.g., name: $new_name, email: $new_email)";
$log_stmt->bind_param("is", $user_id, $description);
$log_stmt->execute();
$log_stmt->close();
} else {
echo "Error logging action: " . $conn->error;
}



        // Redirect to the same page to refresh the budget data
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Budgets</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Chart.js Library -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
body {
    font-family: 'Poppins', sans-serif;
    color: #333;
    background-image: url('homeimage.jpg'); /* Background image */
    background-size: cover; /* Ensure the background image covers the entire page */
    background-repeat: no-repeat; /* Prevent image repetition */
    background-position: center; /* Center the background image */
    display: flex;
    flex-direction: column;
}


body {
    font-family: 'Poppins', sans-serif;
    color: #333;
    display: flex; /* Make body a flex container */
    flex-direction: column; /* Stack content vertically */
}

.container {
    flex: 1; /* Makes the container take the remaining space */
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    background-color: rgba(255, 255, 255, 0.5); /* Slight transparency */
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.8);
    border-radius: 8px;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

table, th, td {
    border: 1px solid #ddd;
}

th, td {
    padding: 12px;
    text-align: center;
}

th {
    background-color: #4b8e8d; /* Teal shade for the header */
    color: white;
}

td {
    background-color: #f9f9f9; /* Light background for table cells */
}

/* Style for the custom error message alert */
.custom-alert {
    background-color: #f8d7da; /* Light red background */
    color: #721c24; /* Dark red text */
    border: 1px solid #f5c6cb; /* Light red border */
    padding: 15px;
    border-radius: 6px;
    font-size: 1rem;
    margin-top: 20px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.custom-alert .alert-icon {
    margin-right: 10px;
}

.custom-alert a {
    color: #721c24; /* Dark red link color */
    text-decoration: none;
    font-weight: bold;
}

.custom-alert a:hover {
    text-decoration: underline;
}

form {
    margin-top: 30px;
    padding: 30px;
    background-color: rgba(255, 255, 255, 0.8); /* Slight transparency for form */
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

form label {
    font-weight: bold;
}

form select, form input[type="number"], form input[type="date"] {
    width: 100%;
    padding: 10px;
    margin: 10px 0;
    border-radius: 6px;
    border: 1px solid #ddd;
    font-size: 1rem;
}

form button {
    background-color: #4b8e8d; /* Teal button */
    color: white;
    padding: 12px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 1rem;
    width: 100%;
}

form button:hover {
    background-color: #3a7470; /* Darker shade of teal on hover */
}

.currency-selector {
    display: flex;
    justify-content: center;
    margin-top: 20px;
}

.currency-selector select {
    padding: 10px;
    font-size: 1rem;
    border-radius: 6px;
    border: 1px solid #ddd;
    width: 200px;
}

.graph-container {
    margin-top: 40px;
    display: flex;
    justify-content: center;
}

.back-link {
    display: block;
    text-align: center;
    margin-top: 20px;
    font-size: 1rem;
    text-decoration: none;
    color: #4b8e8d; /* Teal color for the link */
}

.back-link:hover {
    text-decoration: underline;
}

#custom_category_div {
    display: none;
    margin-top: 10px;
}

#custom_category {
    padding: 10px;
    font-size: 1rem;
    border-radius: 6px;
    width: 100%;
    border: 1px solid #ddd;
}

footer {
    background-color: #f8f9fa;
    color: #4b8e8d; /* Teal color for footer text */
    text-align: center;
    padding: 20px 0;
    position: relative;
    bottom: 0;
    width: 100%;
}

footer .container {
    display: flex;
    flex-direction: column;
    align-items: center;
}

footer .row {
    display: flex;
    justify-content: space-between;
    width: 100%;
}

footer .col-lg-3, footer .col-md-6 {
    margin: 10px 0;
}

footer .list-unstyled {
    padding-left: 0;
    list-style: none;
}

footer .list-unstyled a {
    color: #333;
    text-decoration: none;
}

footer .list-unstyled a:hover {
    text-decoration: underline;
}

footer .text-center {
    font-size: 14px;
    padding-top: 20px;
}

@media (max-width: 768px) {
    .container {
        padding: 15px;
        margin: 10px;
    }

    table {
        font-size: 0.9rem;
    }
}

    </style>
</head>
<body>

    <!-- Top Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
      <div class="container-fluid">
        <a class="navbar-brand" href="#">MegaCash</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav ms-auto">
            <li class="nav-item">
              <a class="nav-link " href="dashboard.php">Dashboard</a>
            </li>
            <li class="nav-item">
              <a class="nav-link active" href="budget.php">MANAGE BUDGETS</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="expense.php">Expenses</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="my_profile.php">Profile</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="reports.php">Notifications</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="log_out.php">log out</a>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    

    <div class="container">
        <h1>Manage Your Budgets</h1>

        <!-- Currency Selector -->
        <div class="currency-selector">
            <form method="POST" action="budget.php">
                <label>Select Currency:</label>
                <select name="currency" onchange="this.form.submit()">
                    <?php foreach ($currencies as $key => $symbol): ?>
                        <option value="<?php echo $key; ?>" <?php echo $currency == $key ? 'selected' : ''; ?>>
                            <?php echo $symbol . " " . $key; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <!-- Budget Form -->
        <form method="POST" action="budget.php">
            <label for="category">Category</label>
            <select name="category" id="category" required onchange="toggleCustomCategory()">
                <option value="">Select a category</option>
                <option value="housing">Housing</option>
                <option value="food">Food</option>
                <option value="health">Health</option>
                <option value="Entertainment">Entertainment</option>
                <option value="Utilities">Utilities</option>
                <option value="Transportation">Transportation</option>
                <option value="Savings">Savings</option>
                <option value="Other">Other (Custom)</option>
            </select>

            <div id="custom_category_div">
                <label for="custom_category">Custom Category Name</label>
                <input type="text" name="custom_category" id="custom_category">
            </div>

            <label for="amount">Amount</label>
            <input type="number" name="amount" id="amount" required min="0" step="0.01">

            <label for="start_date">Start Date</label>
            <input type="date" name="start_date" id="start_date" required>

            <label for="end_date">End Date</label>
            <input type="date" name="end_date" id="end_date" required>

            <button type="submit">Save Budget</button><br>
        </form>

        <?php if (!empty($error_message)): ?>
    <div id="error-alert" class="alert alert-danger custom-alert" role="alert">
        <span class="alert-icon">&#9888;</span> <!-- Warning Icon -->
        <?= htmlspecialchars($error_message) ?>
    </div>
<?php endif; ?>

<script>
    // Function to hide the alert after 15 seconds
    setTimeout(function() {
        var alertBox = document.getElementById('error-alert');
        if (alertBox) {
            alertBox.style.display = 'none';
        }
    }, 15000); // 15 seconds (15000 milliseconds)
</script>


        
        <!-- Budget Table -->
    <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Amount</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($budget as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['category']); ?></td>
                        <td><?php echo $currencies[$currency] . " " . number_format($item['amount'], 2); ?></td>
                        <td><?php echo date('Y-m-d', strtotime($item['start_date'])); ?></td>
                        <td><?php echo date('Y-m-d', strtotime($item['end_date'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Link to go back to Dashboard -->
        <a class="back-link" href="dashboard.php">Back to Dashboard</a>

    </div>

    

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <ul class="list-unstyled">
                        <li><a href="about.php">About</a></li>
                        <li><a href="contact.php">Contact</a></li>
                        <li><a href="privacy.php">Privacy Policy</a></li>
                    </ul>
                </div>
                <div class="col-md-6 text-center">
                    <p>&copy; 2024 MegaCash. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script>
        function toggleCustomCategory() {
            var categorySelect = document.getElementById('category');
            var customCategoryDiv = document.getElementById('custom_category_div');
            if (categorySelect.value === 'Other') {
                customCategoryDiv.style.display = 'block';
            } else {
                customCategoryDiv.style.display = 'none';
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
