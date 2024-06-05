<?php
session_start();

// Database configuration
$servername = "localhost";
$username = "root";
$password = ""; // Default password for root is usually empty in XAMPP
$dbname = "registration_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to sanitize user input
function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Initialize variables and set to empty values
$firstname = $lastname = $email = $password = "";
$firstnameErr = $lastnameErr = $emailErr = $passwordErr = "";
$loginErr = "";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['register'])) {
        // Registration form processing
        $isValid = true;

        // Validate first name
        if (empty($_POST["firstname"])) {
            $firstnameErr = "First name is required";
            $isValid = false;
        } else {
            $firstname = test_input($_POST["firstname"]);
            if (!preg_match("/^[a-zA-Z-' ]*$/", $firstname)) {
                $firstnameErr = "Only letters and white space allowed";
                $isValid = false;
            }
        }

        // Validate last name
        if (empty($_POST["lastname"])) {
            $lastnameErr = "Last name is required";
            $isValid = false;
        } else {
            $lastname = test_input($_POST["lastname"]);
            if (!preg_match("/^[a-zA-Z-' ]*$/", $lastname)) {
                $lastnameErr = "Only letters and white space allowed";
                $isValid = false;
            }
        }

        // Validate email
        if (empty($_POST["email"])) {
            $emailErr = "Email is required";
            $isValid = false;
        } else {
            $email = test_input($_POST["email"]);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $emailErr = "Invalid email format";
                $isValid = false;
            }
        }

        // Validate password
        if (empty($_POST["password"])) {
            $passwordErr = "Password is required";
            $isValid = false;
        } else {
            $password = test_input($_POST["password"]);
            if (strlen($password) < 6) {
                $passwordErr = "Password must be at least 6 characters long";
                $isValid = false;
            }
        }

        // If all inputs are valid, insert into the database
        if ($isValid) {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Prepare the SQL statement
            $stmt = $conn->prepare("INSERT INTO users (firstname, lastname, email, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $firstname, $lastname, $email, $hashed_password);

            // Execute the statement
            if ($stmt->execute()) {
                echo "Registration successful!";
            } else {
                if ($stmt->errno == 1062) {
                    echo "Error: This email is already registered.";
                } else {
                    echo "Error: " . $stmt->error;
                }
            }

            // Close the statement
            $stmt->close();
        }
    } elseif (isset($_POST['login'])) {
        // Login form processing
        $email = test_input($_POST["email"]);
        $password = test_input($_POST["password"]);

        // Validate email and password
        if (!empty($email) && !empty($password)) {
            // Prepare and bind
            $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($id, $hashed_password);
                $stmt->fetch();

                // Verify password
                if (password_verify($password, $hashed_password)) {
                    // Login successful
                    $_SESSION['loggedin'] = true;
                    $_SESSION['userid'] = $id;
                    $_SESSION['email'] = $email;
                    echo "Login successful!";
                    // You can start a session and redirect the user to another page if needed
                } else {
                    $loginErr = "Invalid password.";
                }
            } else {
                $loginErr = "No account found with that email.";
            }

            // Close statement
            $stmt->close();
        } else {
            $loginErr = "Please fill in all fields.";
        }
    }
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="footer.css">
    <link rel="stylesheet" href="register.css">
    <title>Login & Registration</title>
</head>
<body>
    <div class="wrapper">
        <nav class="nav">
            <div class="nav-logo">
                <p>LOGO .</p>
            </div>
            <div class="nav-menu" id="navMenu">
                <ul>
                    <li><a href="home.php" class="link">Home</a></li>
                    <li><a href="Products.php" class="link">Products</a></li>
                    <li><a href="about.php" class="link">About</a></li>
                </ul>
            </div>
            <div class="nav-button">
                <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                    <button class="btn" id="logoutBtn" onclick="logout()">Logout</button>
                <?php else: ?>
                    <button class="btn white-btn" id="loginBtn" onclick="login()">Sign In</button>
                    <button class="btn" id="registerBtn" onclick="register()">Sign Up</button>
                <?php endif; ?>
            </div>
            <div class="nav-menu-btn">
                <i class="bx bx-menu" onclick="myMenuFunction()"></i>
            </div>
        </nav>

        <div class="form-box">
            <div class="login-container" id="login">
                <div class="top">
                    <span>
                        Don't have an account?
                        <a href="#" onclick="register()">Sign Up</a>
                    </span>
                    <header>Login</header>
                </div>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="input-box">
                        <input type="text" class="input-field" placeholder="Username or Email" name="email" />
                        <i class="bx bx-user"></i>
                    </div>
                    <div class="input-box">
                        <input type="password" class="input-field" placeholder="Password" name="password" />
                        <i class="bx bx-lock-alt"></i>
                    </div>
                    <div class="input-box">
                        <input type="submit" class="submit" value="Sign In" name="login" />
                    </div>
                    <div class="two-col">
                        <div class="one">
                            <input type="checkbox" id="login-check" />
                            <label for="login-check"> Remember Me</label>
                        </div>
                        <div class="two">
                            <label><a href="#">Forgot password?</a></label>
                        </div>
                    </div>
                    <span><?php echo $loginErr; ?></span>
                </form>
            </div>

            <div class="register-container" id="register">
                <div class="top">
                    <span>
                        Have an account? <a href="#" onclick="login()">Login</a>
                    </span>
                    <header>Sign Up</header>
                </div>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="two-forms">
                        <div class="input-box">
                            <input type="text" class="input-field" placeholder="Firstname" name="firstname" />
                            <i class="bx bx-user"></i>
                            <span><?php echo $firstnameErr; ?></span>
                        </div>
                        <div class="input-box">
                            <input type="text" class="input-field" placeholder="Lastname" name="lastname" />
                            <i class="bx bx-user"></i>
                            <span><?php echo $lastnameErr; ?></span>
                        </div>
                    </div>
                    <div class="input-box">
                        <input type="text" class="input-field" placeholder="Email" name="email" />
                        <i class="bx bx-envelope"></i>
                        <span><?php echo $emailErr; ?></span>
                    </div>
                    <div class="input-box">
                        <input type="password" class="input-field" placeholder="Password" name="password" />
                        <i class="bx bx-lock-alt"></i>
                        <span><?php echo $passwordErr; ?></span>
                    </div>
                    <div class="input-box">
                        <input type="submit" class="submit" value="Register" name="register" />
                    </div>
                    <div class="two-col">
                        <div class="one">
                            <input type="checkbox" id="register-check" />
                            <label for="register-check"> Remember Me</label>
                        </div>
                        <div class="two">
                            <label><a href="#">Terms & conditions</a></label>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <footer>
        <p></p>
        <div class="copy">
            &copy; All rights reserved to &nbsp;
            <span> AZA<span class="tech">TECH</span></span>
        </div>
        <ul class="social-icons">
            <li>
                <a href="#"><i class="ri-facebook-fill"></i></a>
                <p>Facebook Account</p>
            </li>
            <li>
                <a href="#"><i class="ri-twitter-fill"></i></a>
                <p>Twitter Account</p>
            </li>
            <li>
                <a href="#"><i class="ri-instagram-fill"></i></a>
                <p>Instagram Account</p>
            </li>
        </ul>
    </footer>
    <script src="register.js"></script>
    <script>
        function myMenuFunction() {
            var i = document.getElementById("navMenu");

            if (i.className === "nav-menu") {
                i.className += " responsive";
            } else {
                i.className = "nav-menu";
            }
        }

        var a = document.getElementById("loginBtn");
        var b = document.getElementById("registerBtn");
        var x = document.getElementById("login");
        var y = document.getElementById("register");

        function login() {
            x.style.left = "4px";
            y.style.right = "-520px";
            a.className += " white-btn";
            b.className = "btn";
            x.style.opacity = 1;
            y.style.opacity = 0;
        }

        function register() {
            x.style.left = "-510px";
            y.style.right = "5px";
            a.className = "btn";
            b.className += " white-btn";
            x.style.opacity = 0;
            y.style.opacity = 1;
        }

        function logout() {
            window.location.href = 'logout.php';
        }
    </script>
</body>
</html>
