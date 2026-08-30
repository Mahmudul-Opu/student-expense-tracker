<?php

require_once "config/auth.php";
require_once "config/db.php";
require_once "config/csrf.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: transactions.php");
    exit;
}

verifyCsrfToken();

$id = filter_input(
    INPUT_POST,
    "id",
    FILTER_VALIDATE_INT
);

if (!$id) {
    header("Location: transactions.php");
    exit;
}

$stmt = $pdo->prepare(
    "DELETE FROM transactions
     WHERE id = ?
     AND user_id = ?"
);

$stmt->execute([
    $id,
    $_SESSION["user_id"]
]);

header("Location: transactions.php?deleted=1");
exit;