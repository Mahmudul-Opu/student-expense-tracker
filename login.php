<?php

session_start();

require_once "config/db.php";
require_once "config/csrf.php";

if (isset($_SESSION["user_id"])) {
    header("Location: dashboard.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verifyCsrfToken();

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {

        $error = "Email and password are required.";

    } else {

        $stmt = $pdo->prepare(
            "SELECT id, name, email, password
             FROM users
             WHERE email = ?"
        );

        $stmt->execute([$email]);

        $user = $stmt->fetch();

        if (
            $user &&
            password_verify(
                $password,
                $user["password"]
            )
        ) {

            session_regenerate_id(true);

            $_SESSION["user_id"] = $user["id"];
            $_SESSION["user_name"] = $user["name"];

            header("Location: dashboard.php");
            exit;

        } else {

            $error = "Invalid email or password.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Login | Expense Tracker</title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body>

<div class="auth-container">

    <h1>Expense Tracker</h1>

    <h2>Login</h2>

    <?php if (isset($_GET["registered"])): ?>

        <div class="message success">
            Registration successful. Please login.
        </div>

    <?php endif; ?>

    <?php if ($error !== ""): ?>

        <div class="message error">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>

    <form method="POST">

        <label for="email">
            Email
        </label>

        <input
            type="email"
            name="email"
            id="email"
            required
        >

        <label for="password">
            Password
        </label>

        <input
            type="password"
            name="password"
            id="password"
            required
        >

        <button type="submit">
            Login
        </button>

        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars(csrfToken()) ?>"
        >

    </form>

    <p>
        Don't have an account?
        <a href="register.php">Register</a>
    </p>

</div>

</body>

</html>