<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Welcome to MegaCash - Your ultimate finance app.">
    <title>MegaCash</title>
    <style>
        /* Resetting default margin and padding */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Poppins', sans-serif;
            color: #333;
            background-image: url('budget_background.jpg'); /* Background image */
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

        header {
            background-color: #4B8E8D;
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .logo {
            display: flex;
            align-items: center;
        }
        .logo-icon {
            width: 50px;
            height: 50px;
            background-color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 0.5rem;
        }
        .logo-icon span {
            font-size: 1.5rem;
            font-weight: bold;
            color: #4B8E8D;
        }
        .title {
            font-size: 1.5rem;
            color: #fff;
            font-weight: bold;
        }
        nav {
            display: flex;
            gap: 1rem;
        }
        nav a, nav .register-link {
            color: #fff;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s ease;
        }
        nav a:hover, nav .register-link:hover {
            color: #FFD700;
        }
        nav .register-link {
            background-color: #FFD700;
            color: #4B8E8D;
            padding: 0.5rem 1rem;
            border-radius: 5px;
        }
        main {
            flex: 1;
            text-align: center;
            padding: 2rem;
        }
        h1 {
            font-size: 2.5rem;
            color: #4B8E8D;
            margin-bottom: 1rem;
        }
        p {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 2rem;
        }
        .features {
            display: flex;
            justify-content: space-around;
            margin-top: 2rem;
        }
        .feature {
            width: 30%;
            padding: 1rem;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .feature h2 {
            color: #4B8E8D;
            margin-bottom: 0.5rem;
        }
        .cta {
            margin-top: 3rem;
            text-align: center;
        }
        .cta button {
            padding: 0.8rem 1.5rem;
            background-color: #4B8E8D;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .cta button:hover {
            background-color: #3A7470;
        }
        footer {
            background-color: #333;
            color: #fff;
            text-align: center;
            padding: 1rem;
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
            <a href="register.php" class="register-link">Register Now</a>
            <a href="log_out.php" class="login-link">Login Now</a>
        </nav>
    </header>
    <main>
        <h1>MegaCash</h1>
        <p>Your ultimate finance management tool. Keep track of your finances with ease and style.</p>
        <div id="features" class="features">
            <div class="feature">
                <h2>Track Expenses</h2>
                <p>Monitor your spending habits and stay on top of your budget with real-time updates.</p>
            </div>
            <div class="feature">
                <h2>Set Goals</h2>
                <p>Define financial goals and track your progress towards achieving them.</p>
            </div>
            <div class="feature">
                <h2>Secure Transactions</h2>
                <p>Enjoy the highest level of security for all your financial transactions.</p>
            </div>
        </div>
        <div id="about" class="cta">
            <h2>About MegaCash</h2>
            <p>We are committed to simplifying your financial management and helping you achieve your financial dreams. MegaCash combines ease of use with advanced tools to give you the best experience.</p>
            <button>Learn More</button>
        </div>
        <div id="contact" class="cta">
            <h2>Contact Us</h2>
            <p>Have questions or feedback? We'd love to hear from you.</p>
            <button>Contact Support</button>
        </div>
    </main>
    <footer>
        &copy; 2024 MegaCash. All rights reserved.
    </footer>
</body>
</html>
