<nav>

    <strong>
        Expense Tracker
    </strong>

    <div class="nav-links">

        <span class="user-name">
            Hello,
            <?= htmlspecialchars(
                $_SESSION["user_name"]
            ) ?>
        </span>

        <a href="dashboard.php">
            Dashboard
        </a>

        <a href="transactions.php">
            Transactions
        </a>

        <a href="add_transaction.php">
            Add Transaction
        </a>

        <form
            method="POST"
            action="logout.php"
            class="inline-form"
        >

            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars(
                    csrfToken()
                ) ?>"
            >

            <button
                type="submit"
                class="nav-button"
            >
                Logout
            </button>

        </form>

    </div>

</nav>