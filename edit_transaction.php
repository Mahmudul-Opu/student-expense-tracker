<?php

require_once "config/auth.php";
require_once "config/db.php";
require_once "config/csrf.php";
require_once "config/validation.php";

$id = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);

if (!$id) {
    header("Location: transactions.php");
    exit;
}

$stmt = $pdo->prepare(
    "SELECT *
     FROM transactions
     WHERE id = ?
     AND user_id = ?"
);

$stmt->execute([
    $id,
    $_SESSION["user_id"]
]);

$transaction = $stmt->fetch();

if (!$transaction) {
    header("Location: transactions.php");
    exit;
}

$stmt = $pdo->query(
    "SELECT id, name
     FROM categories
     ORDER BY name ASC"
);

$categories = $stmt->fetchAll();

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verifyCsrfToken();

    $type = $_POST["type"] ?? "";

    $categoryId =
        (int) ($_POST["category_id"] ?? 0);

    $amount =
        trim($_POST["amount"] ?? "");

    $description =
        trim($_POST["description"] ?? "");

    $transactionDate =
        $_POST["transaction_date"] ?? "";

    if (
        $type === "" ||
        $categoryId <= 0 ||
        $amount === "" ||
        $transactionDate === ""
    ) {

        $error = "Please fill in all required fields.";

    } elseif (
        !in_array(
            $type,
            ["income", "expense"],
            true
        )
    ) {

        $error = "Invalid transaction type.";

    } elseif (
        !is_numeric($amount) ||
        (float) $amount <= 0
    ) {

        $error = "Amount must be greater than 0.";

    } else {

        $categoryStmt = $pdo->prepare(
            "SELECT id
             FROM categories
             WHERE id = ?"
        );

        $categoryStmt->execute([$categoryId]);

        if (!$categoryStmt->fetch()) {

            $error = "Invalid category.";

        } else {

            $stmt = $pdo->prepare(
                "UPDATE transactions

                 SET
                    category_id = ?,
                    type = ?,
                    amount = ?,
                    description = ?,
                    transaction_date = ?

                 WHERE id = ?
                 AND user_id = ?"
            );

            $stmt->execute([
                $categoryId,
                $type,
                $amount,
                $description,
                $transactionDate,
                $id,
                $_SESSION["user_id"]
            ]);

            header(
                "Location: transactions.php?updated=1"
            );

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

    <title>Edit Transaction</title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>

<body>

<?php require "includes/navbar.php"; ?>

<main class="container">

    <h1>Edit Transaction</h1>

    <?php if ($error !== ""): ?>

        <div class="message error">

            <?= htmlspecialchars($error) ?>

        </div>

    <?php endif; ?>

    <form method="POST" class="transaction-form">

        <label for="type">
            Transaction Type
        </label>

        <select
            name="type"
            id="type"
            required
        >

            <option
                value="income"
                <?= $transaction["type"] === "income"
                    ? "selected"
                    : "" ?>
            >
                Income
            </option>

            <option
                value="expense"
                <?= $transaction["type"] === "expense"
                    ? "selected"
                    : "" ?>
            >
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

            <?php foreach ($categories as $category): ?>

                <option
                    value="<?= $category["id"] ?>"

                    <?= (int) $category["id"] ===
                        (int) $transaction["category_id"]
                        ? "selected"
                        : "" ?>
                >

                    <?= htmlspecialchars(
                        $category["name"]
                    ) ?>

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
            value="<?= htmlspecialchars(
                $transaction["amount"]
            ) ?>"
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
        ><?= htmlspecialchars(
            $transaction["description"] ?? ""
        ) ?></textarea>

        <label for="transaction_date">
            Date
        </label>

        <input
            type="date"
            name="transaction_date"
            id="transaction_date"
            value="<?= htmlspecialchars(
                $transaction["transaction_date"]
            ) ?>"
            required
        >

        <button type="submit">
            Update Transaction
        </button>
        
        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars(csrfToken()) ?>"
        >

    </form>

</main>

</body>
</html>