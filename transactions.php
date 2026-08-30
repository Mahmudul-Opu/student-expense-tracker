<?php

require_once "config/auth.php";
require_once "config/db.php";
require_once "config/csrf.php";
require_once "config/validation.php";

// --------------------------------------------------
// Get and sanitize filter values
// --------------------------------------------------

$search = trim($_GET["search"] ?? "");
$type = $_GET["type"] ?? "";
$categoryId = (int) ($_GET["category_id"] ?? 0);
$fromDate = $_GET["from_date"] ?? "";
$toDate = $_GET["to_date"] ?? "";


// --------------------------------------------------
// Validate date filters
// --------------------------------------------------

$filterError = "";

if ($fromDate !== "" && !isValidDate($fromDate)) {
    $filterError = "Invalid starting date.";
}

if ($toDate !== "" && !isValidDate($toDate)) {
    $filterError = "Invalid ending date.";
}

if (
    $fromDate !== "" &&
    $toDate !== "" &&
    isValidDate($fromDate) &&
    isValidDate($toDate) &&
    $fromDate > $toDate
) {
    $filterError =
        "The starting date cannot be after the ending date.";
}


// Only allow known transaction types.
if (!in_array($type, ["", "income", "expense"], true)) {
    $type = "";
}


// --------------------------------------------------
// Load categories
// --------------------------------------------------

$categoryStmt = $pdo->query(
    "SELECT id, name
     FROM categories
     ORDER BY name ASC"
);

$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);


// --------------------------------------------------
// Build transaction query
// --------------------------------------------------

$sql = "
    SELECT
        transactions.id,
        transactions.type,
        transactions.amount,
        transactions.description,
        transactions.transaction_date,
        categories.name AS category_name
    FROM transactions
    INNER JOIN categories
        ON transactions.category_id = categories.id
    WHERE transactions.user_id = ?
";

$params = [
    $_SESSION["user_id"]
];


// Search by description.
if ($search !== "") {
    $sql .= "
        AND transactions.description LIKE ?
    ";

    $params[] = "%" . $search . "%";
}


// Filter by type.
if ($type !== "") {
    $sql .= "
        AND transactions.type = ?
    ";

    $params[] = $type;
}


// Filter by category.
if ($categoryId > 0) {
    $sql .= "
        AND transactions.category_id = ?
    ";

    $params[] = $categoryId;
}


// Filter by starting date.
if ($fromDate !== "" && isValidDate($fromDate)) {
    $sql .= "
        AND transactions.transaction_date >= ?
    ";

    $params[] = $fromDate;
}


// Filter by ending date.
if ($toDate !== "" && isValidDate($toDate)) {
    $sql .= "
        AND transactions.transaction_date <= ?
    ";

    $params[] = $toDate;
}


// Newest transactions first.
$sql .= "
    ORDER BY
        transactions.transaction_date DESC,
        transactions.id DESC
";


// --------------------------------------------------
// Execute query
// --------------------------------------------------

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);


// --------------------------------------------------
// Helper for HTML escaping
// --------------------------------------------------

function e($value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        "UTF-8"
    );
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

    <title>Transactions | Expense Tracker</title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>

<body>

<?php require "includes/navbar.php"; ?>


<main class="container">

    <div class="page-header">

        <h1>Transactions</h1>

        <a
            href="add_transaction.php"
            class="button-link"
        >
            + Add Transaction
        </a>

    </div>


    <!-- Filters -->

    <form
        method="GET"
        action="transactions.php"
        class="filter-form"
    >

        <div>

            <label for="search">
                Search
            </label>

            <input
                type="text"
                id="search"
                name="search"
                placeholder="Search description..."
                value="<?= e($search) ?>"
            >

        </div>


        <div>

            <label for="filter_type">
                Type
            </label>

            <select
                id="filter_type"
                name="type"
            >

                <option value="">
                    All Types
                </option>

                <option
                    value="income"
                    <?= $type === "income" ? "selected" : "" ?>
                >
                    Income
                </option>

                <option
                    value="expense"
                    <?= $type === "expense" ? "selected" : "" ?>
                >
                    Expense
                </option>

            </select>

        </div>


        <div>

            <label for="filter_category">
                Category
            </label>

            <select
                id="filter_category"
                name="category_id"
            >

                <option value="0">
                    All Categories
                </option>

                <?php foreach ($categories as $category): ?>

                    <option
                        value="<?= (int) $category["id"] ?>"
                        <?= $categoryId === (int) $category["id"]
                            ? "selected"
                            : "" ?>
                    >
                        <?= e($category["name"]) ?>
                    </option>

                <?php endforeach; ?>

            </select>

        </div>


        <div>

            <label for="from_date">
                From
            </label>

            <input
                type="date"
                id="from_date"
                name="from_date"
                value="<?= e($fromDate) ?>"
            >

        </div>


        <div>

            <label for="to_date">
                To
            </label>

            <input
                type="date"
                id="to_date"
                name="to_date"
                value="<?= e($toDate) ?>"
            >

        </div>


        <div class="filter-actions">

            <button type="submit">
                Filter
            </button>

            <a
                href="transactions.php"
                class="reset-button"
            >
                Reset
            </a>

        </div>

    </form>


    <!-- Success Messages -->

    <?php if (isset($_GET["added"])): ?>

        <div class="message success">
            Transaction added successfully.
        </div>

    <?php endif; ?>


    <?php if (isset($_GET["updated"])): ?>

        <div class="message success">
            Transaction updated successfully.
        </div>

    <?php endif; ?>


    <?php if (isset($_GET["deleted"])): ?>

        <div class="message success">
            Transaction deleted successfully.
        </div>

    <?php endif; ?>


    <!-- Filter Error -->

    <?php if ($filterError !== ""): ?>

        <div class="message error">
            <?= htmlspecialchars($filterError) ?>
        </div>

    <?php endif; ?>


    <!-- Transactions -->

    <?php if (empty($transactions)): ?>

        <p>
            No transactions found.
        </p>

    <?php else: ?>

        <div class="table-wrapper">

            <table>

                <thead>

                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Amount</th>
                        <th>Actions</th>
                    </tr>

                </thead>


                <tbody>

                <?php foreach ($transactions as $transaction): ?>

                    <tr>

                        <!-- Date -->

                        <td>
                            <?= e($transaction["transaction_date"]) ?>
                        </td>


                        <!-- Type -->

                        <td>

                            <span class="<?= e($transaction["type"]) ?>">

                                <?= e(ucfirst($transaction["type"])) ?>

                            </span>

                        </td>


                        <!-- Category -->

                        <td>
                            <?= e($transaction["category_name"]) ?>
                        </td>


                        <!-- Description -->

                        <td>

                            <?php if (
                                isset($transaction["description"]) &&
                                trim($transaction["description"]) !== ""
                            ): ?>

                                <?= e($transaction["description"]) ?>

                            <?php else: ?>

                                —

                            <?php endif; ?>

                        </td>


                        <!-- Amount -->

                        <td>

                            ₹<?= number_format(
                                (float) $transaction["amount"],
                                2
                            ) ?>

                        </td>


                        <!-- Actions -->

                        <td>

                            <a
                                href="edit_transaction.php?id=<?= (int) $transaction["id"] ?>"
                            >
                                Edit
                            </a>


                            <form
                                method="POST"
                                action="delete_transaction.php"
                                class="inline-form delete-form"
                            >

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= (int) $transaction["id"] ?>"
                                >

                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?= e(csrfToken()) ?>"
                                >

                                <button
                                    type="submit"
                                    class="delete-button"
                                >
                                    Delete
                                </button>

                            </form>

                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    <?php endif; ?>

</main>


<script src="js/script.js"></script>

</body>

</html>