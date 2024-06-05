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

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product page</title>
    <link rel="stylesheet" href="css.css">
    <link rel="stylesheet" href="nav.css">
    <link rel="stylesheet" href="footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

</head>

<body>
    <header>
        <div class="wrapper">
        <nav class="nav">
            <div class="nav-logo">
                <p>LOGO .</p>
            </div>
            <div class="nav-menu" id="navMenu">
                <ul>
                    <li><a href="home.php" class="link">Home</a></li>
                    <li><a href="Products.php" class="link active">Products</a></li>
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
        </div>
      </header>

    <p style="color:#ffe978be ; font-size: 160px; text-align: center; "> Starry Night </p>
    <div class="text_under_name">
        <p style="text-align: center; margin-top: 30px; color: #ffffff;">Lorem ipsum dolor sit amet consectetur
            adipisicing
            elit.
            Aperiam rem sed quia,
            quidem, amet quo reiciendis
            eveniet esse autem, vitae eligendi libero voluptates unde est aspernatur quaerat ex non dolore.</p>
    </div>
    <div class="aboveproducts">


        <div class=" image_above_product">
            <div style="opacity: 0.6;">
                <img src="Images/arts.jpg" style="margin-bottom: 20px ;" alt="">

            </div>

            <div class="bottom-right"> <span style="margin-top: 20px; color: #c2a3a3;">
                    <br> Vincent van Gogh <br>
                    The Starry Night <br>
                    Saint Rémy, June 1889 <br>
                </span></div>
            <div class="centered">Fine Arts </div>
            <div class="centered1"> Important Paintings & Sculpture </div>
            <a href="#Finearts">
                <div class="centered2"> <button class="button-shopSowFineArts" role="button">Shop Now!</button>
                </div>
            </a>

        </div>
    </div>


    <div class="container" id="Finearts">
        <div class="productcard">
            <div class="image">
                <img src="Images/product1.jpg" alt="">
            </div>
            <div class="productdetail">
                <h2>Birth Of Venus</h2>
                <p>
                    shows the goddess of love and beauty arriving on land, on the island of Cyprus, born of the sea
                    spray and blown there by the winds, Zephyr and, perhaps, Aura. </p>
                <div>
                    <div
                        style="background-color: rgba(0, 0, 0, 0.794); width: auto; height: 25px; border-radius: 6px; text-align: center; margin-top: 10px; display: flex; align-content: space-around; align-items:end;">
                        <p
                            style="color: rgb(239, 191, 0); margin-left: 5px; font-size: 20px; font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;">
                            $300
                        </p>
                        <a href="#">
                            <i class="fa-solid fa-cart-plus"
                                style="color: #ffffff; margin-top: 20px; margin-right: 30px; height: 10px; width: 10px; margin-left: 70px; margin-right: 70px; margin-bottom: 10px;"></i></a>
                        <a href="#" style="margin-bottom: 4px;"> Bid Now!</a>
                    </div>

                </div>
            </div>

        </div>
        <div class="productcard">
            <div class="image" style="margin-left: 30px;">
                <img src="Images/product2.png" alt="">
            </div>
            <div class="productdetail">
                <h2>the scream</h2>
                <p style="font-size: 13px;">
                    The Scream is a proto-expressionist artwork realized by Norwegian painter Edvard Munch in 1893. It
                    depicts a deformed human figure disturbingly screaming in a landscape with unnatural colors. </p>
                <div>
                    <div
                        style="background-color: rgba(0, 0, 0, 0.794); width: auto; height: 25px; border-radius: 6px; text-align: center; margin-top: 5px; display: flex; align-content: space-around; align-items:end;">
                        <p
                            style="color: rgb(239, 191, 0); margin-left: 5px; font-size: 20px; font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;">
                            $12,300
                        </p>
                        <a href="#">
                            <i class="fa-solid fa-cart-plus"
                                style="color: #ffffff; margin-top: 20px; margin-right: 30px; height: 10px; width: 10px; margin-left: 40px; margin-right: 40px; margin-bottom: 10px;"></i></a>
                        <a href="#" style="margin-bottom: 4px;"> Bid Now!</a>
                    </div>

                </div>
            </div>
        </div>
        <div class="productcard">
            <div class="image">
                <img src="Images/product3.png" alt="">
            </div>
            <div class="productdetail">
                <h2>Guernica</h2>
                <p>
                    is a large 1937 oil painting by Spanish artist Pablo Picasso. It is one of his best-known
                    works,regarded by many art critics as the most moving and powerful anti-war painting in history.
                </p>
                <div>
                    <div
                        style="background-color: rgba(0, 0, 0, 0.794); width: auto; height: 25px; border-radius: 6px; text-align: center; margin-top: 15px; display: flex; align-content: space-around; align-items:end;">
                        <p
                            style="color: rgb(239, 191, 0); margin-left: 5px; font-size: 20px; font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;">
                            $103,300
                        </p>
                        <a href="#">
                            <i class="fa-solid fa-cart-plus"
                                style="color: #ffffff; margin-top: 20px; margin-right: 30px; height: 10px; width: 10px; margin-left: 45px; margin-right: 45px; margin-bottom: 10px;"></i></a>
                        <a href="#" style="margin-bottom: 4px;"> Bid Now!</a>
                    </div>

                </div>
            </div>
        </div>
        <div class="productcard " style="margin-top: 100px;">
            <div class="image">
                <img src="Images/product4.png" alt="">
            </div>
            <div class="productdetail">
                <h2>Girl With a Pearl Earring </h2>
                <p style="font-size: 13px;">
                    is an oil painting by Dutch Golden Age painter Johannes Vermeer, dated c. 1665. Going by various
                    names over the centuries, it became known by its present title towards the end of the 20th century
                    after the earring worn by the girl portrayed there. </p>
                <div>
                    <div
                        style="background-color: rgba(0, 0, 0, 0.794); width: auto; height: 25px; border-radius: 6px; text-align: center; margin-top: 5px; display: flex; align-content: space-around; align-items:end;">
                        <p
                            style="color: rgb(239, 191, 0); margin-left: 5px; font-size: 20px; font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;">
                            $192,300
                        </p>
                        <a href="#">
                            <i class="fa-solid fa-cart-plus"
                                style="color: #ffffff; margin-top: 20px; margin-right: 30px; height: 10px; width: 10px; margin-left: 45px; margin-right: 45px; margin-bottom: 10px;"></i></a>
                        <a href="#" style="margin-bottom: 4px;"> Bid Now!</a>
                    </div>

                </div>
            </div>
        </div>
        <div class="productcard" style="margin-top: 100px;">
            <div class="image">
                <img src="Images/product5.jpg" alt="">
            </div>
            <div class="productdetail">
                <h2>
                    Andy Warhol Birth of Venus
                </h2>
                <p>
                    Warhol first became fascinated by Renaissance paintings after attending The Mona Lisa’s first
                    exhibition in New York in 1963. </p>
                <div
                    style="background-color: rgba(0, 0, 0, 0.794); width: auto; height: 25px; border-radius: 6px; text-align: center; margin-top: 35px; display: flex; align-content: space-around; align-items:end;">
                    <p
                        style="color: rgb(239, 191, 0); margin-left: 5px; font-size: 20px; font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;">
                        $330
                    </p>
                    <a href="#">
                        <i class="fa-solid fa-cart-plus"
                            style="color: #ffffff; margin-top: 20px; margin-right: 30px; height: 10px; width: 10px; margin-left: 70px; margin-right: 70px; margin-bottom: 10px;"></i></a>
                    <a href="#" style="margin-bottom: 4px;"> Bid Now!</a>
                </div>
            </div>
        </div>
        <div class="productcard" style="margin-top: 100px;">
            <div class="image">
                <img src="Images/product6.jpg" alt="">
            </div>
            <div class="productdetail">
                <h2>Las Meninas</h2>
                <p style="font-size: 13.6px;">
                    is a 1656 painting in the Museo del Prado in Madrid, by Diego Velázquez, the leading artist of the
                    Spanish Baroque. It has become one of the most widely analyzed works in Western painting. </p>
                <div
                    style="background-color: rgba(0, 0, 0, 0.794); width: auto; height: 25px; border-radius: 6px; text-align: center; margin-top: 5px; display: flex; align-content: space-around; align-items:end;">
                    <p
                        style="color: rgb(239, 191, 0); margin-left: 5px; font-size: 20px; font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;">
                        $2,600
                    </p>
                    <a href="#">
                        <i class="fa-solid fa-cart-plus"
                            style="color: #ffffff; margin-top: 20px; margin-right: 30px; height: 10px; width: 10px; margin-left: 50px; margin-right: 50px; margin-bottom: 10px;"></i></a>
                    <a href="#" style="margin-bottom: 4px;"> Bid Now!</a>
                </div>
            </div>
        </div>
        <div class="productcard" style="margin-top: 100px;">
            <div class="image">
                <img src="Images/product7.jpg" alt="">
            </div>
            <div class="productdetail">
                <h2>Whistler's Mother </h2>
                <p style="font-size: 13px;">
                    Regarded as a potent symbol of motherhood the atmospheric work depicts Whistler's mother,Anna,
                    sitting in profile clutching a white handkerchief, and is painted in tonal shades of grey and black.
                </p>
                <div
                    style="background-color: rgba(0, 0, 0, 0.794); width: auto; height: 25px; border-radius: 6px; text-align: center; margin-top: 10px; display: flex; align-content: space-around; align-items:end;">
                    <p
                        style="color: rgb(239, 191, 0); margin-left: 5px; font-size: 20px; font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;">
                        $910
                    </p>
                    <a href="#">
                        <i class="fa-solid fa-cart-plus"
                            style="color: #ffffff; margin-top: 20px; margin-right: 30px; height: 10px; width: 10px; margin-left: 70px; margin-right: 70px; margin-bottom: 10px;"></i></a>
                    <a href="#" style="margin-bottom: 4px;"> Bid Now!</a>
                </div>
            </div>
        </div>
        <div class="productcard" style="margin-top: 100px;">
            <div class="image">
                <img src="Images/product8.jpg" alt="">
            </div>
            <div class="productdetail">
                <h2> The Night Watch </h2>
                <p style="font-size: 13.3px;">
                    Rembrandt's largest and most famous painting was made for one of the three headquarters of
                    Amsterdam's civic guard. These groups of civilian soldiers defended the city from attack. Rembrandt
                    was the first to paint all of the figures in a civic guard piece in action. </p>
                <div
                    style="background-color: rgba(0, 0, 0, 0.794); width: auto; height: 25px; border-radius: 6px; text-align: center; margin-top: 5px; display: flex; align-content: space-around; align-items:end;">
                    <p
                        style="color: rgb(239, 191, 0); margin-left: 5px; font-size: 20px; font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;">
                        $1,300,000
                    </p>
                    <a href="#">
                        <i class="fa-solid fa-cart-plus"
                            style="color: #ffffff; margin-top: 20px; margin-right: 30px; height: 10px; width: 10px; margin-left: 40px; margin-right: 40px; margin-bottom: 10px;"></i></a>
                    <a href="#" style="margin-bottom: 4px;"> Bid Now!</a>
                </div>
            </div>
        </div>
    </div>

    <!--
        first 8 products          from 1 till 8                       --------------
        ---------------------------------------------------------------------
        -----------------------------------------------------------------------
        -----------arts-------------------------------                arts
    -->
    <div class="aboveproductsJew;">


        <div class=" image_above_productJew">
            <div style="opacity: 0.7;">
                <img src="Images/jewelry.png" style="margin-bottom: 20px ; width: 1350px; padding:20px ;" alt="">
            </div>

            <div class="centeredJ"> Jewelry </div>
            <div class="centered1J"> For more than a century, M.S. Rau has acquired the most iconic,<br> sublime and
                legendary pieces. The rarest diamonds,<br> the most breathtaking gems and the pinnacles of craftsmanship
                <br>
                merge in these stunning creations.
            </div>
            <a href="#Jewelry">
                <div class="centered2J"> <button class="button-Jewelry" role="button">Shop Now!</button>
                </div>
            </a>

        </div>



        <div class="container" id="Jewelry">
            <div class="productcard">
                <div class="image">
                    <img src="Images/product9.webp" alt="">
                </div>
                <div class="productdetail">
                    <h2 style="margin-top: -45px;">Duke Of Windsor Enamel Cufflinks
                    </h2>
                    <p>
                        Hand painted portraits of Queen Alexandra and King Edward VII adorn each terminal,The cufflinks
                        were likely a gift from King Edward VII, the Duke's grandfather </p>
                    <div
                        style="background-color: rgba(0, 0, 0, 0.794); width: auto; height: 25px; border-radius: 6px; text-align: center; margin-top: 5px; display: flex; align-content: space-around; align-items:end;">
                        <p
                            style="color: rgb(239, 191, 0); margin-left: 5px; font-size: 20px; font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;">
                            $88,500 </p>
                        <a href="#">
                            <i class="fa-solid fa-cart-plus"
                                style="color: #ffffff; margin-top: 20px; margin-right: 30px; height: 10px; width: 10px; margin-left: 40px; margin-right: 40px; margin-bottom: 10px;"></i></a>
                        <a href="#" style="margin-bottom: 4px;"> Bid Now!</a>
                    </div>
                </div>
            </div>
            <div class="productcard">
                <div class="image">
                    <img src="Images/product10.jpg" alt="">
                </div>
                <div class="productdetail">
                    <h2 style="margin-top: -10px; font-size: 20px;">Classic Australian Black Opal </h2>
                    <p>
                        This striking and unique pendant features an impressive 26.04-carat Australian black op
                        The AGL certifies the the quality of the opal, noting its desirable pinfire pattern </p>
                    <div
                        style="background-color: rgba(0, 0, 0, 0.794); width: auto; height: 25px; border-radius: 6px; text-align: center; margin-top: 5px; display: flex; align-content: space-around; align-items:end;">
                        <p
                            style="color: rgb(239, 191, 0); margin-left: 5px; font-size: 20px; font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;">
                            $360,000
                        </p>
                        <a href="#">
                            <i class="fa-solid fa-cart-plus"
                                style="color: #ffffff; margin-top: 20px; margin-right: 30px; height: 10px; width: 10px; margin-left: 40px; margin-right: 40px; margin-bottom: 10px;"></i></a>
                        <a href="#" style="margin-bottom: 4px;"> Bid Now!</a>
                    </div>
                </div>
            </div>
            <div class="productcard">
                <div class="image">
                    <img src="Images/product11.jpg" alt="">
                </div>
                <div class="productdetail">
                    <h2 style="margin-top: -30px;">Antique Burma Ruby And Diamond Necklace</h2>
                    <p>
                        A work of Victorian splendor, this necklace features Burma rubies and brilliant diamonds
                        The Burma rubies total approximately 50 carats </p>
                    <div
                        style="background-color: rgba(0, 0, 0, 0.794); width: auto; height: 25px; border-radius: 6px; text-align: center; margin-top: 15px; display: flex; align-content: space-around; align-items:end;">
                        <p
                            style="color: rgb(239, 191, 0); margin-left: 5px; font-size: 20px; font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;">
                            $685,000
                        </p>
                        <a href="#">
                            <i class="fa-solid fa-cart-plus"
                                style="color: #ffffff; margin-top: 20px; margin-right: 30px; height: 10px; width: 10px; margin-left: 40px; margin-right: 40px; margin-bottom: 10px;"></i></a>
                        <a href="#" style="margin-bottom: 4px;"> Bid Now!</a>
                    </div>
                </div>
            </div>
            <div class="productcard" style="margin-top: 100px;">
                <div class="image" style="margin-left: 30px;">
                    <img src="Images/product12.jpg" alt="">
                </div>
                <div class="productdetail">
                    <h2 style="margin-top: -20px;">Grande Sonnerie Pocket Watch By Montandon</h2>
                    <p>
                        This rare and complex grand sonnerie pocket watch was crafted by Swiss firm, Montandon A grande
                        sonnerie chimes on the hours and quarters automatically </p>
                    <div
                        style="background-color: rgba(0, 0, 0, 0.794); width: auto; height: 25px; border-radius: 6px; text-align: center; margin-top: 5px; display: flex; align-content: space-around; align-items:end;">
                        <p
                            style="color: rgb(239, 191, 0); margin-left: 5px; font-size: 20px; font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;">
                            $28,850 </p>
                        <a href="#">
                            <i class="fa-solid fa-cart-plus"
                                style="color: #ffffff; margin-top: 20px; margin-right: 30px; height: 10px; width: 10px; margin-left: 40px; margin-right: 40px; margin-bottom: 10px;"></i></a>
                        <a href="#" style="margin-bottom: 4px;"> Bid Now!</a>
                    </div>
                </div>
            </div>
            <div class="productcard" style="margin-top: 100px;">
                <div class="image" style="margin-left: 30px;">
                    <img src="Images/product13.jpg" alt="">
                </div>
                <div class="productdetail">
                    <h2 style="margin-top: -20px;">Cartier Art Deco Guillotine Purse Watch</h2>
                    <p>
                        his Guillotine purse watch was crafted by the revered Cartier
                        Crafted of 18K yellow gold, no detail was spared by the famed firm in creating a luxury
                        timepiece </p>
                    <div
                        style="background-color: rgba(0, 0, 0, 0.794); width: auto; height: 25px; border-radius: 6px; text-align: center; margin-top: 5px; display: flex; align-content: space-around; align-items:end;">
                        <p
                            style="color: rgb(239, 191, 0); margin-left: 5px; font-size: 20px; font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;">
                            $38,850 </p>
                        <a href="#">
                            <i class="fa-solid fa-cart-plus"
                                style="color: #ffffff; margin-top: 20px; margin-right: 30px; height: 10px; width: 10px; margin-left: 40px; margin-right: 40px; margin-bottom: 10px;"></i></a>
                        <a href="#" style="margin-bottom: 4px;"> Bid Now!</a>
                    </div>
                </div>
            </div>
            <div class="productcard" style="margin-top: 100px;">
                <div class="image" style="margin-top: -10px; margin-left: 30px;">
                    <img src="Images/product14.jpg" alt="">
                </div>
                <div class="productdetail">
                    <h2 style="margin-top: -15px;">Rolex GMT-Master Rootbeer Watch </h2>
                    <p>
                        This vintage GMT-Master wristwatch was made by world-renowned watchmakers Rolex
                        First made for pilots. </p>
                    <div
                        style="background-color: rgba(0, 0, 0, 0.794); width: auto; height: 25px; border-radius: 6px; text-align: center; margin-top: 25px; display: flex; align-content: space-around; align-items:end;">
                        <p
                            style="color: rgb(239, 191, 0); margin-left: 5px; font-size: 20px; font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;">
                            $22,850 </p>
                        <a href="#">
                            <i class="fa-solid fa-cart-plus"
                                style="color: #ffffff; margin-top: 20px; margin-right: 30px; height: 10px; width: 10px; margin-left: 40px; margin-right: 40px; margin-bottom: 10px;"></i></a>
                        <a href="#" style="margin-bottom: 4px;"> Bid Now!</a>
                    </div>
                </div>
            </div>
            <div class="productcard" style="margin-top: 100px;">
                <div class="image" style="margin-left: 30px;">
                    <img src="Images/product15.jpg" alt="">
                </div>
                <div class="productdetail">
                    <h2 style="margin-top: -30px;">Fancy Vivid Yellow Diamond Dragonfly Brooch</h2>
                    <p>
                        This dragonfly brooch was commissioned by Fred and made by Carvin ,This amazing brooch features
                        60.26 total carats of fancy diamonds. </p>
                    <div
                        style="background-color: rgba(0, 0, 0, 0.794); width: auto; height: 25px; border-radius: 6px; text-align: center; margin-top: 5px; display: flex; align-content: space-around; align-items:end;">
                        <p
                            style="color: rgb(239, 191, 0); margin-left: 5px; font-size: 20px; font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;">
                            $985,000 </p>
                        <a href="#">
                            <i class="fa-solid fa-cart-plus"
                                style="color: #ffffff; margin-top: 20px; margin-right: 30px; height: 10px; width: 10px; margin-left: 40px; margin-right: 40px; margin-bottom: 10px;"></i></a>
                        <a href="#" style="margin-bottom: 4px;"> Bid Now!</a>
                    </div>
                </div>
            </div>
            <div class="productcard" style="margin-top: 100px;">
                <div class="image" style="margin-top: -10px; margin-left: 30px;">
                    <img src="Images/product16.jpg" alt="">
                </div>
                <div class="productdetail">
                    <h2 style="margin-top: -30px;">Rose Cut Diamond Necklace And Earrings </h2>
                    <p>
                        This set features rare rose and brilliant cut diamonds, totaling over 61 carats
                        Rose cut diamonds are historic, rare and difficult to find on the market. </p> <a href="#">
                        <div
                            style="background-color: rgba(0, 0, 0, 0.794); width: auto; height: 25px; border-radius: 6px; text-align: center; margin-top: 25px; display: flex; align-content: space-around; align-items:end;">
                            <p
                                style="color: rgb(239, 191, 0); margin-left: 5px; font-size: 20px; font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;">
                                $550,000
                            </p>
                            <a href="#">
                                <i class="fa-solid fa-cart-plus"
                                    style="color: #ffffff; margin-top: 20px; margin-right: 30px; height: 10px; width: 10px; margin-left: 40px; margin-right: 40px; margin-bottom: 10px;"></i></a>
                            <a href="#" style="margin-bottom: 4px;"> Bid Now!</a>
                        </div>
                </div>
            </div>
        </div>
    </div>
    <!--
        second 8 products         from 9 till 16                        --------------
        ---------------------------------------------------------------------
        -----------------------------------------------------------------------
        -----------jewlery-------------------------------                jewlery
    -->
    <div class="aboveproductsJew " style="background-color: #22333D; height: auto;">


        <div class=" image_above_productJew">
            <div style="opacity: 0.8; ">
                <img src="Images/Antique.png" style="margin-bottom: 20px ; width: 1350px; padding:20px ;" alt="">

            </div>


            <div class="centeredAntique"> Antique </div>
            <div class="centeredAntique1">

                <br>
                From The 17th Through The 20th Centuries<br>Crystal, Art Glass,<br> American Brilliant Period Cut Glass
                <br>
                Baccarat, Tiffany, Meissen Chandeliers, Lamps Orchestrion, <br>Cylinder & Disk Music Boxes Tiffany,
                Storr,
                Bateman, De Lamerie & More
            </div>
            <a href="#Antique">
                <div class="centered2"> <button class="buttonAntique" role="button">Shop Now!</button>
                </div>
            </a>

        </div>
    </div>
    <div style="background-color: #22333D;margin-top: -150px;">
        <div class="container " id="Antique">
            <div class="productcard">
                <div class="image">
                    <img src="Images/product17.jpg" alt="">
                </div>
                <div class="productdetail">
                    <h2 style="margin-top: -20px;">Russian Imperial Silver Punch</h2>
                    <p style="font-size: 14px;">
                        This exquisite silver punch set was made for Czar Alexander III as a gift to British Captain
                        Joseph Captain Wiggins made a pioneering expedition along the Yenisei River </p>
                    <div
                        style="background-color: rgba(0, 0, 0, 0.794); width: auto; height: 25px; border-radius: 6px; text-align: center; margin-top: 10px; display: flex; align-content: space-around; align-items:end;">
                        <p
                            style="color: rgb(239, 191, 0); margin-left: 5px; font-size: 20px; font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;">
                            $498,000 </p>
                        <a href="#">
                            <i class="fa-solid fa-cart-plus"
                                style="color: #ffffff; margin-top: 20px; margin-right: 30px; height: 10px; width: 10px; margin-left: 40px; margin-right: 40px; margin-bottom: 10px;"></i></a>
                        <a href="#" style="margin-bottom: 4px;"> Bid Now!</a>
                    </div>
                </div>
            </div>
            <div class="productcard">
                <div class="image">
                    <img src="Images/product18.png" alt="">
                </div>
                <div class="productdetail">
                    <h2 style="margin-top: -20px;">Paul Storr Silver Tea Urn</h2>
                    <p>
                        This incredible Georgian silver tea urn was crafted by the legendary Paul Storr
                        Created for nobility, this urn bears the hallmarks of Storr's undeniable genius as a silversmith
                    </p>
                    <div
                        style="background-color: rgba(0, 0, 0, 0.794); width: auto; height: 25px; border-radius: 6px; text-align: center; margin-top: 15px; display: flex; align-content: space-around; align-items:end;">
                        <p
                            style="color: rgb(239, 191, 0); margin-left: 5px; font-size: 20px; font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;">
                            $88,500 </p>
                        <a href="#">
                            <i class="fa-solid fa-cart-plus"
                                style="color: #ffffff; margin-top: 20px; margin-right: 30px; height: 10px; width: 10px; margin-left: 40px; margin-right: 40px; margin-bottom: 10px;"></i></a>
                        <a href="#" style="margin-bottom: 4px;"> Bid Now!</a>
                    </div>
                </div>
            </div>
            <div class="productcard">
                <div class="image">
                    <img src="Images/product19.jpg" alt="">
                </div>
                <div class="productdetail">
                    <h2 style="margin-top: -10px;">Silver-Gilt Presentation Ewer By R. & S. Garrard</h2>
                    <p>
                        This exceptional silver-gilt ewer was crafted by R.&S.Garrard of London The creation is marked
                        by a large mask. </p>
                    <div
                        style="background-color: rgba(0, 0, 0, 0.794); width: auto; height: 25px; border-radius: 6px; text-align: center; margin-top: 10px; display: flex; align-content: space-around; align-items:end;">
                        <p
                            style="color: rgb(239, 0, 0); margin-left: 5px; font-size: 13px; font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif; margin-bottom: 6px;">
                            Price Upon Request </p>
                        <a href="#">
                            <i class="fa-solid fa-cart-plus"
                                style="color: #ffffff; margin-top: 20px; margin-right: 30px; height: 10px; width: 10px; margin-left: 40px; margin-right: 40px; margin-bottom: 10px;"></i></a>
                        <a href="#" style="margin-bottom: 4px;"> Bid Now!</a>
                    </div>
                </div>
            </div>
            <div class="productcard" style="margin-top: 100px;">
                <div class="image" style="margin-top: -30px;">
                    <img src="Images/product20.png" alt="">
                </div>
                <div class="productdetail">
                    <h2 style="margin-top: -20px;">Battle Of Trafalgar Bicentennial Silver-Gilt Casket</h2>
                    <p style="font-size: 13.75px;">
                        Made for the bicentenary of the Battle of Trafalgar, this casket is one of only five of its kind
                        The silver-gilt commemorative casket immortalizes Lord Nelson's legacy in captivating form. </p>
                    <div
                        style="background-color: rgba(0, 0, 0, 0.794); width: auto; height: 25px; border-radius: 6px; text-align: center; margin-top: 3px; display: flex; align-content: space-around; align-items:end;">
                        <p
                            style="color: rgb(239, 191, 0); margin-left: 5px; font-size: 20px; font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;">
                            $48,850 </p>
                        <a href="#">
                            <i class="fa-solid fa-cart-plus"
                                style="color: #ffffff; margin-top: 20px; margin-right: 30px; height: 10px; width: 10px; margin-left: 40px; margin-right: 40px; margin-bottom: 10px;"></i></a>
                        <a href="#" style="margin-bottom: 4px;"> Bid Now!</a>
                    </div>
                </div>
            </div>
            <div class="productcard" style="margin-top: 100px;">
                <div class="image" style="margin-top: -10px; margin-left: 30px;">
                    <img src="Images/product21.png" alt="">
                </div>
                <div class="productdetail">
                    <h2 style="margin-top: -20px;">Paul Frey Miniature Gold And Jade Clock
                    </h2>
                    <p>
                        This incredible 18K gold and jade clock was created by the renowned Paul Frrey
                        Frey was a famed French jewelry designer and manufacturer in Paris. </p>
                    <div
                        style="background-color: rgba(0, 0, 0, 0.794); width: auto; height: 25px; border-radius: 6px; text-align: center; margin-top: 10px; display: flex; align-content: space-around; align-items:end;">
                        <p
                            style="color: rgb(239, 191, 0); margin-left: 5px; font-size: 20px; font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;">
                            $98,500

                        </p>
                        <a href="#">
                            <i class="fa-solid fa-cart-plus"
                                style="color: #ffffff; margin-top: 20px; margin-right: 30px; height: 10px; width: 10px; margin-left: 40px; margin-right: 40px; margin-bottom: 10px;"></i></a>
                        <a href="#" style="margin-bottom: 4px;"> Bid Now!</a>
                    </div>
                </div>
            </div>
            <div class="productcard" style="margin-top: 100px;">
                <div class="image" style="margin-top: -10px;  margin-left: 30px;">
                    <img src="Images/product22.png" alt="">
                </div>
                <div class="productdetail">
                    <h2 style="margin-top: -14px;">French Annular Dial Mantel
                    </h2>
                    <p>
                        This rare French annular dial mantel clock features a unique pendulum movement
                        The exquisite ormolu work was crafted by Maison Marnyhac of Paris,The clock's strike and Brocot.
                    </p>
                    <div
                        style="background-color: rgba(0, 0, 0, 0.794); width: auto; height: 25px; border-radius: 6px; text-align: center; margin-top: 13px; display: flex; align-content: space-around; align-items:end;">
                        <p
                            style="color: rgb(239, 191, 0); margin-left: 5px; font-size: 20px; font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;">
                            $68,500
                        </p>
                        <a href="#">
                            <i class="fa-solid fa-cart-plus"
                                style="color: #ffffff; margin-top: 20px; margin-right: 30px; height: 10px; width: 10px; margin-left: 40px; margin-right: 40px; margin-bottom: 10px;"></i></a>
                        <a href="#" style="margin-bottom: 4px;"> Bid Now!</a>
                    </div>
                </div>
            </div>
            <div class="productcard" style="margin-top: 100px;">
                <div class="image " style="margin-top: -30px; margin-left: 30px;">
                    <img src="Images/product23.png" alt="">
                </div>
                <div class="productdetail">
                    <h2 style="margin-top: -17px;">Russian Figural Bronze Candelabra</h2>
                    <p>
                        These Russian figural bronze candelabra are incredibly beautiful and prized
                        Their elegant interpretation of the French Louis XVI taste is striking.</p>
                    <div
                        style="background-color: rgba(0, 0, 0, 0.794); width: auto; height: 25px; border-radius: 6px; text-align: center; margin-top: 15px; display: flex; align-content: space-around; align-items:end;">
                        <p
                            style="color: rgb(239, 191, 0); margin-left: 5px; font-size: 20px; font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;">
                            $74,500
                        </p>
                        <a href="#">
                            <i class="fa-solid fa-cart-plus"
                                style="color: #ffffff; margin-top: 20px; margin-right: 30px; height: 10px; width: 10px; margin-left: 40px; margin-right: 40px; margin-bottom: 10px;"></i></a>
                        <a href="#" style="margin-bottom: 4px;"> Bid Now!</a>
                    </div>
                </div>
            </div>
            <div class="productcard" style="margin-top: 100px;">
                <div class="image">
                    <img src="Images/product24.png" alt="">
                </div>
                <div class="productdetail">
                    <h2 style="margin-top: -20px;">Egyptian Empire Bedroom
                    </h2>
                    <p>
                        This seven-piece bedroom suite was almost certainly owned by 19th century Egyptian royalty
                        It was a special commission executed by the premier Parisian ébéniste, Antoine Krieger. </p>
                    <div
                        style="background-color: rgba(0, 0, 0, 0.794); width: auto; height: 25px; border-radius: 6px; text-align: center; margin-top: 15px; display: flex; align-content: space-around; align-items:end;">
                        <p
                            style="color: rgb(239, 191, 0); margin-left: 5px; font-size: 20px; font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;">
                        </p>
                        <a href="#">
                            <i class="fa-solid fa-cart-plus"
                                style="color: #ffffff; margin-top: 20px; margin-right: 30px; height: 10px; width: 10px; margin-left: 40px; margin-right: 40px; margin-bottom: 10px;"></i></a>
                        <a href="#" style="margin-bottom: 4px;"> Bid Now!</a>
                    </div>
                </div>
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
    <!--
        last 8 products     from 17 till 25                           --------------
        ---------------------------------------------------------------------
        -----------------------------------------------------------------------
        -----------antique-------------------------------                antique
    -->
    <script src="register.js"></script>
    <script>
        function logout() {
            window.location.href = 'logout.php';
        }
    </script>
</body>

</html>