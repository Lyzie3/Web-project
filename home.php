<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Welcome to MegaCash - Your ultimate finance app.">
    <title>MegaCash</title>
    <style>
        /* Global Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Body Styles */
        body {
            font-family: 'Poppins', sans-serif;
            color: #333;
            background-image: linear-gradient(to right, rgba(75, 142, 141, 0.3), rgba(153, 102, 255, 0.2)), url('budget_background.jpg');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Header */
        header {
            background-color: rgba(0, 0, 0, 0.1);
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .logo {
            display: flex;
            align-items: center;
        }

        .logo-icon {
            width: 50px;
            height: 50px;
            background-color: rgba(153, 102, 255, 0.4);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 0.8rem;
        }

        .logo-icon span {
            font-size: 1.5rem;
            font-weight: bold;
            color: #4B8E8D;
        }

        .title {
            font-size: 1.8rem;
            color: rgba(153, 102, 255, 0.4);
            font-weight: bold;
        }

        nav {
            display: flex;
            gap: 1.5rem;
        }

        nav a, nav .register-link {
            color: rgba(153, 102, 255, 0.2);
            text-decoration: none;
            font-weight: bold;
            font-size: 1rem;
            transition: color 0.3s ease;
        }

        nav a:hover, nav .register-link:hover {
            color: #fff;
        }

        nav .register-link {
            background-color: #4B8E8D;
            color: #fff;
            padding: 0.5rem 1rem;
            border-radius: 5px;
        }

        /* Main Content */
        main {
            flex: 1;
            text-align: center;
            padding: 3rem 2rem;
            color: #fff;
        }

        h1 {
            font-size: 3rem;
            color:white;
            margin-bottom: 1.5rem;
        }
        
        p2{
            font-size:1.5rem;
            color:#4b8e8d;
        }
        p {
            font-size: 1.2rem;
            color: #4b8e8d;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        /* Features Section */
        .features {
            display: flex;
            justify-content: center;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .feature {
            flex: 1;
            max-width: 300px;
            padding: 1.5rem;
            background-color: rgba(255, 255, 255, 0.7);
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            text-align: center;
            color: #333;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .feature:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
        }

        .feature h2 {
            color: #4B8E8D;
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }

        .feature p {
            font-size: 1rem;
            color: #555;
        }

        /* Call to Action Section */
        .cta {
            margin-top: 3rem;
        }

        .cta button {
            padding: 0.8rem 2rem;
            background-color: rgba(153, 102, 255, 0.2);
            color: #4B8E8D;
            border: none;
            border-radius: 5px;
            font-size: 1.2rem;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .cta button:hover {
            background-color: #4B8E8D;
            color: #fff;
            transform: translateY(-3px);
        }

        footer {
            background-color: #45;
            color: #aaa;
            text-align: center;
            padding: 1rem 2rem;
            font-size: 0.9rem;
        }

        footer a {
            color: rgba(153, 102, 255, 0.4);
            text-decoration: none;
            font-weight: bold;
        }

        footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <div class="logo-icon">
                <span>MC</span>
            </div>
            <span class="title">MegaCash</span>
        </div>
        <nav>
            <a href="register.php" class="register-link">Register</a>
            <a href="log_in.php" class="login-link">Login</a>
        </nav>
    </header>
    <main>
        <h1> MegaCash Welcomes You</h1>
        <p2>Take control of your finances with MegaCash. Track expenses, set budget goals, and secure your future with ease.</p2><br><br>
        <div class="features">
            <div class="feature">
                <h2>Track Expenses</h2>
                <p>Monitor your spending habits and stay on top of your budget with real-time updates.</p>
            </div>
            <div class="feature">
                <h2>Set Budgets</h2>
                <p>State defined budgets and track your progress towards achieving them.</p>
            </div>
            <div class="feature">
                <h2>Secure Transactions</h2>
                <p>Enjoy the highest level of security for all your financial deals.</p>
            </div>
        </div>
        
        <a  href="https://futureeducationmagazine.com/teach-money-management-to-students/">know more about finance management</a>
    </main>
    <footer>
        &copy; 2024 MegaCash. All rights reserved. | <a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a>
    </footer>
</body>
</html>
