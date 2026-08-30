const registerForm =
    document.getElementById("registerForm");

if (registerForm) {

    registerForm.addEventListener(
        "submit",
        function (event) {

            const password =
                document.getElementById("password").value;

            const confirmPassword =
                document.getElementById("confirm_password").value;

            if (password.length < 6) {

                event.preventDefault();

                alert(
                    "Password must be at least 6 characters."
                );

                return;
            }

            if (password !== confirmPassword) {

                event.preventDefault();

                alert("Passwords do not match.");
            }
        }
    );
}

const deleteForms =
    document.querySelectorAll(".delete-form");

deleteForms.forEach(function (form) {

    form.addEventListener("submit", function (event) {

        const confirmed = confirm(
            "Are you sure you want to delete this transaction?"
        );

        if (!confirmed) {
            event.preventDefault();
        }
    });
});