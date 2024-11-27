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

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $gender = $_POST['gender'];
    $date_of_birth = $_POST['date_of_birth'];
    $year_of_study = $_POST['year_of_study'];
    $program = $_POST['program'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    try {
        $check_query = "SELECT COUNT(*) FROM users WHERE username = :username OR email = :email";
        $stmt = $conn->prepare($check_query);
        $stmt->execute([':username' => $username, ':email' => $email]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $error_message = 'Username or email already exists. Please choose another.';
        } else {
            $sql = "INSERT INTO users (username, first_name, last_name, gender, date_of_birth, year_of_study, program, email, password) 
                    VALUES (:username, :first_name, :last_name, :gender, :date_of_birth, :year_of_study, :program, :email, :password)";
            $stmt = $conn->prepare($sql);

            $stmt->execute([
                ':username' => $username,
                ':first_name' => $first_name,
                ':last_name' => $last_name,
                ':gender' => $gender,
                ':date_of_birth' => $date_of_birth,
                ':year_of_study' => $year_of_study,
                ':program' => $program,
                ':email' => $email,
                ':password' => $password
            ]);

            header('Location: log_in.php');
            exit;
        }
    } catch (PDOException $e) {
        $error_message = "An error occurred: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #F4F4F9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        form {
            width: 100%;
            max-width: 800px;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #4B8E8D;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input, select, button {
            width: 94%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
        }

        button {
            background-color: #4B8E8D;
            color: #fff;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }

        button:hover {
            background-color: #3A7470;
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

        .strength-bar {
            height: 10px;
            width: 100%;
            margin-top: -5px;
            border-radius: 5px;
            background-color: #ddd;
            position: relative;
        }

        .strength-bar > div {
            height: 100%;
            width: 0;
            border-radius: 5px;
        }

        .strength-text {
            font-size: 0.9rem;
            color: #4B8E8D;
        }

        p.error {
            color: red;
            font-size: 0.9rem;
        }
    </style>
    <script>
        function checkPasswordStrength(password) {
            const strengthBar = document.getElementById('strength-bar-fill');
            const strengthText = document.getElementById('strength-text');
            const weakRegex = /^[a-zA-Z0-9]{6,}$/;
            const strongRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;

            if (strongRegex.test(password)) {
                strengthBar.style.width = '100%';
                strengthBar.style.backgroundColor = 'green';
                strengthText.innerText = 'Strong';
            } else if (weakRegex.test(password)) {
                strengthBar.style.width = '50%';
                strengthBar.style.backgroundColor = 'orange';
                strengthText.innerText = 'Moderate';
            } else {
                strengthBar.style.width = '25%';
                strengthBar.style.backgroundColor = 'red';
                strengthText.innerText = 'Weak';
            }
        }
    </script>
</head>
<body>
    <form method="POST" action="register.php">
        <h1>Register</h1>
        <?php if ($error_message): ?>
            <p class="error"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <label for="username">Username:</label>
        <input id="username" type="text" name="username" required>

        <label for="first_name">First Name:</label>
        <input id="first_name" type="text" name="first_name" required>

        <label for="last_name">Last Name:</label>
        <input id="last_name" type="text" name="last_name" required>

        <label for="gender">Gender:</label>
        <select id="gender" name="gender" required>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
            <option value="Other">Other</option>
        </select>

        <label for="date_of_birth">Date of Birth:</label>
        <input id="date_of_birth" type="date" name="date_of_birth" required>

        <label for="year_of_study">Year of Study:</label>
        <input id="year_of_study" type="number" name="year_of_study" required>

        <label for="program">Program:</label>
        <input id="program" type="text" name="program" required>

        <label for="email">Email:</label>
        <input id="email" type="email" name="email" required>

        <label for="password">Password:</label>
        <input id="password" type="password" name="password" oninput="checkPasswordStrength(this.value)" required>
        <div class="strength-bar"><div id="strength-bar-fill"></div></div>
        <p id="strength-text" class="strength-text">Password strength</p>

        <label>
            <input type="checkbox" name="terms" required> I agree to the <a href="terms.php">Terms and Conditions</a> and <a href="privacy.php">Privacy Policy</a>.
        </label>

        <button type="submit">Register</button>
        <p>Already have an account? <a href="log_in.php">Login here</a></p>
    </form>
</body>
</html>
