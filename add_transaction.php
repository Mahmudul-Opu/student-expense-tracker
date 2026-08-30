<?php

require_once "config/auth.php";
require_once "config/db.php";
require_once "config/csrf.php";
require_once "config/validation.php";

$error = "";
$success = "";

$stmt = $pdo->query(
    "SELECT id, name
     FROM categories
     ORDER BY name ASC"
);

$categories = $stmt->fetchAll();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verifyCsrfToken();

    $type = $_POST["type"] ?? "";
    $categoryId = (int) ($_POST["category_id"] ?? 0);
    $amount = trim($_POST["amount"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $transactionDate = $_POST["transaction_date"] ?? "";

    if (
        $type === "" ||
        $categoryId <= 0 ||
        $amount === "" ||
        $transactionDate === ""
    ) {
        $error = "Please fill in all required fields.";

    } elseif (!in_array($type, ["income", "expense"], true)) {

        $error = "Invalid transaction type.";

    } elseif (!is_numeric($amount) || (float) $amount <= 0) {

        $error = "Amount must be greater than 0.";

    } elseif (!isValidDate($transactionDate)) {

        $error = "Please enter a valid date.";

    } elseif (strlen($description) > 255) {

        $error = "Description cannot exceed 255 characters.";

    } else {

        $stmt = $pdo->prepare(
            "SELECT id
             FROM categories
             WHERE id = ?"
        );

        $stmt->execute([$categoryId]);

        if (!$stmt->fetch()) {

            $error = "Invalid category.";

        } else {

            $stmt = $pdo->prepare(
                "INSERT INTO transactions
                (
                    user_id,
                    category_id,
                    type,
                    amount,
                    description,
                    transaction_date
                )
                VALUES (?, ?, ?, ?, ?, ?)"
            );

            $stmt->execute([
                $_SESSION["user_id"],
                $categoryId,
                $type,
                $amount,
                $description,
                $transactionDate
            ]);

            header("Location: transactions.php?added=1");
            exit;
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

    <title>Add Transaction | Expense Tracker</title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body>

<?php require "includes/navbar.php"; ?>

<main class="container">

    <h1>Add Transaction</h1>

    <?php if ($error !== ""): ?>

        <div class="message error">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>

    <form method="POST" class="transaction-form">

        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars(csrfToken()) ?>"
        >

        <label for="type">
            Transaction Type
        </label>

        <select
            name="type"
            id="type"
            required
        >
            <option value="">
                Select Type
            </option>

            <option value="income">
                Income
            </option>

            <option value="expense">
                Expense
            </option>
        </select>

        <label for="category_id">
            Category
        </label>

        <select
            name="category_id"
            id="category_id"
            required
        >
            <option value="">
                Select Category
            </option>

            <?php foreach ($categories as $category): ?>

                <option value="<?= $category["id"] ?>">
                    <?= htmlspecialchars($category["name"]) ?>
                </option>

            <?php endforeach; ?>

        </select>

        <label for="amount">
            Amount
        </label>

        <input
            type="number"
            name="amount"
            id="amount"
            min="0.01"
            step="0.01"
            required
        >

        <label for="description">
            Description
        </label>

        <textarea
            name="description"
            id="description"
            rows="3"
            maxlength="255"
        ></textarea>

        <label for="transaction_date">
            Date
        </label>

        <input
            type="date"
            name="transaction_date"
            id="transaction_date"
            value="<?= date("Y-m-d") ?>"
            required
        >

        <button type="submit">
            Add Transaction
        </button>

    </form>

</main>

</body>
</html>