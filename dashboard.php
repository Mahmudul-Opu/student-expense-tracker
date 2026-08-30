<?php

require_once "config/auth.php";
require_once "config/db.php";
require_once "config/csrf.php";


// --------------------------------------------------
// Overall summary
// --------------------------------------------------

$stmt = $pdo->prepare(
    "SELECT

        COALESCE(
            SUM(
                CASE
                    WHEN type = 'income'
                    THEN amount
                    ELSE 0
                END
            ),
            0
        ) AS total_income,

        COALESCE(
            SUM(
                CASE
                    WHEN type = 'expense'
                    THEN amount
                    ELSE 0
                END
            ),
            0
        ) AS total_expense

     FROM transactions

     WHERE user_id = ?"
);

$stmt->execute([
    $_SESSION["user_id"]
]);

$summary = $stmt->fetch();

$totalIncome =
    (float) $summary["total_income"];

$totalExpense =
    (float) $summary["total_expense"];

$balance =
    $totalIncome - $totalExpense;


// --------------------------------------------------
// Monthly summary
// --------------------------------------------------

$stmt = $pdo->prepare(
    "SELECT

        COALESCE(
            SUM(
                CASE
                    WHEN type = 'income'
                    THEN amount
                    ELSE 0
                END
            ),
            0
        ) AS monthly_income,

        COALESCE(
            SUM(
                CASE
                    WHEN type = 'expense'
                    THEN amount
                    ELSE 0
                END
            ),
            0
        ) AS monthly_expense

     FROM transactions

     WHERE user_id = ?

     AND transaction_date >=
         DATE_FORMAT(CURDATE(), '%Y-%m-01')

     AND transaction_date <
         DATE_FORMAT(
             CURDATE() + INTERVAL 1 MONTH,
             '%Y-%m-01'
         )"
);

$stmt->execute([
    $_SESSION["user_id"]
]);

$monthlySummary = $stmt->fetch();

$monthlyIncome =
    (float) $monthlySummary["monthly_income"];

$monthlyExpense =
    (float) $monthlySummary["monthly_expense"];


// --------------------------------------------------
// Recent transactions
// --------------------------------------------------

$stmt = $pdo->prepare(
    "SELECT
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

     ORDER BY
        transactions.transaction_date DESC,
        transactions.id DESC

     LIMIT 5"
);

$stmt->execute([
    $_SESSION["user_id"]
]);

$recentTransactions =
    $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Dashboard | Expense Tracker</title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>

<body>

<?php require "includes/navbar.php"; ?>


<main class="container">

    <h1>Dashboard</h1>


    <!-- Overall Summary -->

    <div class="summary-grid">

        <div class="summary-card">

            <h3>Total Income</h3>

            <p class="income">
                ₹<?= number_format(
                    $totalIncome,
                    2
                ) ?>
            </p>

        </div>

        <div class="summary-card">

            <h3>Total Expenses</h3>

            <p class="expense">
                ₹<?= number_format(
                    $totalExpense,
                    2
                ) ?>
            </p>

        </div>

        <div class="summary-card">

            <h3>Balance</h3>

            <p>
                ₹<?= number_format(
                    $balance,
                    2
                ) ?>
            </p>

        </div>

    </div>


    <!-- This Month Summary -->

    <h2>This Month</h2>

    <div class="summary-grid">

        <div class="summary-card">

            <h3>Monthly Income</h3>

            <p class="income">
                ₹<?= number_format(
                    $monthlyIncome,
                    2
                ) ?>
            </p>

        </div>

        <div class="summary-card">

            <h3>Monthly Expenses</h3>

            <p class="expense">
                ₹<?= number_format(
                    $monthlyExpense,
                    2
                ) ?>
            </p>

        </div>

        <div class="summary-card">

            <h3>Monthly Savings</h3>

            <p>
                ₹<?= number_format(
                    $monthlyIncome - $monthlyExpense,
                    2
                ) ?>
            </p>

        </div>

    </div>


    <!-- Recent Transactions -->

    <div class="page-header">

        <h2>Recent Transactions</h2>

        <a
            href="add_transaction.php"
            class="button-link"
        >
            + Add Transaction
        </a>

    </div>


    <?php if (count($recentTransactions) === 0): ?>

        <p>
            No transactions yet.
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
                </tr>

                </thead>

                <tbody>

                <?php foreach ($recentTransactions as $transaction): ?>

                    <tr>

                        <td>
                            <?= htmlspecialchars(
                                $transaction["transaction_date"]
                            ) ?>
                        </td>

                        <td>

                            <span class="<?= htmlspecialchars(
                                $transaction["type"]
                            ) ?>">

                                <?= ucfirst(
                                    htmlspecialchars(
                                        $transaction["type"]
                                    )
                                ) ?>

                            </span>

                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $transaction["category_name"]
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $transaction["description"] ?? ""
                            ) ?>
                        </td>

                        <td>
                            ₹<?= number_format(
                                (float) $transaction["amount"],
                                2
                            ) ?>
                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    <?php endif; ?>

</main>

</body>

</html>