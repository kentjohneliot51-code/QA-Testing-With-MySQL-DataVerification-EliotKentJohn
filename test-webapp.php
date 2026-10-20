
<?php
// Database connection
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "birthday_wishnote";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check database connection
if ($conn->connect_error) {
    die("Database connection failed.");
}

$conn->set_charset("utf8mb4");

$successMessage = "";
$errorMessage = "";

// Process the form after confirmation
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Capture all four form values
    $FirstName  = trim($_POST["FirstName"] ?? "");
    $LastName   = trim($_POST["LastName"] ?? "");
    $GovPosition = trim($_POST["GovPosition"] ?? "");
    $Location   = trim($_POST["Location"] ?? "");

    // Validate the required fields and maximum lengths
    if (
        $FirstName === "" ||
        $LastName === "" ||
        $GovPosition === ""
    ) {
        $errorMessage = "Please complete all required fields.";
    } elseif (
        strlen($FirstName) > 30 ||
        strlen($LastName) > 30 ||
        strlen($GovPosition) > 20 ||
        strlen($Location) > 40
    ) {
        $errorMessage = "One or more fields exceed the allowed length.";
    } else {

        /*
         * INTENTIONAL DATABASE INSERTION FLAW:
         * The Location value is captured above but is deliberately
         * omitted from the INSERT column list and bind_param().
         * Only FirstName, LastName, and GovPosition are inserted.
         */

        $sql = "INSERT INTO main_corrupt_politicians
                (FirstName, LastName, GovPosition)
                VALUES (?, ?, ?)";

        $stmt = $conn->prepare($sql);

        if ($stmt) {

            // INTENTIONAL FLAW: Location is not bound or inserted.
            $stmt->bind_param(
                "sss",
                $FirstName,
                $LastName,
                $GovPosition
            );

            if ($stmt->execute()) {
                $successMessage =
                    "Success! The wish note has been sent.";
            } else {
                $errorMessage =
                    "Error: Unable to save the wish note.";
            }

            $stmt->close();

        } else {
            // Fallback message if the SQL query cannot be prepared.
            $errorMessage =
                "Error: Unable to prepare the database query.";
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

    <title>Kara David's Wish Note</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 30px 16px;
            min-height: 100vh;
            background: #f1f5f9;
            font-family: Arial, Helvetica, sans-serif;
            color: #1e293b;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card {
            width: 100%;
            max-width: 480px;
            padding: 32px;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 8px 28px rgba(15, 23, 42, 0.10);
        }

        h2 {
            margin: 0 0 8px;
            text-align: center;
            color: #2563eb;
            font-size: 27px;
        }

        .subtitle {
            margin: 0 0 26px;
            text-align: center;
            color: #64748b;
            font-size: 14px;
            line-height: 1.5;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-size: 14px;
            font-weight: 600;
            color: #334155;
        }

        .required {
            color: #dc2626;
        }

        input {
            width: 100%;
            padding: 12px 13px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            background: #ffffff;
            font-size: 15px;
            color: #0f172a;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        .hint {
            display: block;
            margin-top: 5px;
            font-size: 12px;
            color: #64748b;
        }

        .submit-btn,
        .cancel-btn,
        .confirm-btn {
            border: none;
            border-radius: 7px;
            padding: 12px 18px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .submit-btn {
            width: 100%;
            margin-top: 5px;
            background: #2563eb;
            color: white;
        }

        .submit-btn:hover,
        .confirm-btn:hover {
            background: #1d4ed8;
        }

        .message {
            padding: 13px 15px;
            margin-bottom: 22px;
            border-radius: 7px;
            font-size: 14px;
            line-height: 1.5;
        }

        .success {
            color: #166534;
            background: #dcfce7;
            border: 1px solid #86efac;
        }

        .error {
            color: #991b1b;
            background: #fee2e2;
            border: 1px solid #fca5a5;
        }

        /* Confirmation modal overlay */
        .modal-overlay {
            position: fixed;
            inset: 0;
            z-index: 1000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 16px;
            background: rgba(15, 23, 42, 0.60);
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal {
            width: 100%;
            max-width: 430px;
            max-height: 90vh;
            overflow-y: auto;
            padding: 27px;
            border-radius: 12px;
            background: #ffffff;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2);
            animation: appear 0.2s ease-out;
        }

        @keyframes appear {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal h3 {
            margin: 0 0 8px;
            color: #2563eb;
            font-size: 22px;
        }

        .modal-description {
            margin: 0 0 20px;
            color: #64748b;
            font-size: 14px;
            line-height: 1.5;
        }

        .review-list {
            margin: 0 0 22px;
            padding: 0;
        }

        .review-item {
            padding: 12px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .review-item dt {
            margin-bottom: 5px;
            color: #64748b;
            font-size: 12px;
            font-weight: 600;
        }

        .review-item dd {
            margin: 0;
            overflow-wrap: anywhere;
            color: #0f172a;
            font-size: 15px;
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .cancel-btn {
            background: #e2e8f0;
            color: #334155;
        }

        .cancel-btn:hover {
            background: #cbd5e1;
        }

        .confirm-btn {
            background: #2563eb;
            color: #ffffff;
        }

        @media (max-width: 480px) {
            body {
                padding: 16px 12px;
            }

            .card {
                padding: 24px 20px;
            }

            h2 {
                font-size: 24px;
            }

            .modal {
                padding: 22px 18px;
            }

            .modal-actions {
                flex-direction: column-reverse;
            }

            .modal-actions button {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <main class="card">
        <h2>Kara David's Wish Note</h2>

        <p class="subtitle">
            Enter the target's information and review the details
            before sending your wish note.
        </p>

        <?php if ($successMessage !== ""): ?>
            <div class="message success" role="status">
                <?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if ($errorMessage !== ""): ?>
            <div class="message error" role="alert">
                <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form id="wishForm" method="POST" action="">
            <div class="form-group">
                <label for="FirstName">
                    Target's First Name <span class="required">*</span>
                </label>
                <input
                    type="text"
                    id="FirstName"
                    name="FirstName"
                    maxlength="30"
                    required
                    autocomplete="given-name"
                    value="<?= htmlspecialchars($_POST['FirstName'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                >
            </div>

            <div class="form-group">
                <label for="LastName">
                    Target's Last Name <span class="required">*</span>
                </label>
                <input
                    type="text"
                    id="LastName"
                    name="LastName"
                    maxlength="30"
                    required
                    autocomplete="family-name"
                    value="<?= htmlspecialchars($_POST['LastName'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                >
            </div>

            <div class="form-group">
                <label for="GovPosition">
                    Target's Government Position <span class="required">*</span>
                </label>
                <input
                    type="text"
                    id="GovPosition"
                    name="GovPosition"
                    maxlength="20"
                    required
                    value="<?= htmlspecialchars($_POST['GovPosition'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                >
            </div>

            <div class="form-group">
                <label for="Location">Target's Location</label>
                <input
                    type="text"
                    id="Location"
                    name="Location"
                    maxlength="40"
                    autocomplete="off"
                    value="<?= htmlspecialchars($_POST['Location'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                >
                <span class="hint">Optional — maximum 40 characters.</span>
            </div>

            <button type="submit" class="submit-btn">
                Review &amp; Send
            </button>
        </form>
    </main>

    <!-- Verification modal -->
    <div
        class="modal-overlay"
        id="verifyModal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="modalTitle"
        aria-describedby="modalDescription"
    >
        <div class="modal">
            <h3 id="modalTitle">Verify Target Data</h3>

            <p class="modal-description" id="modalDescription">
                Please check all the information below before submitting.
            </p>

            <dl class="review-list">
                <div class="review-item">
                    <dt>Target's First Name</dt>
                    <dd id="reviewFirstName"></dd>
                </div>

                <div class="review-item">
                    <dt>Target's Last Name</dt>
                    <dd id="reviewLastName"></dd>
                </div>

                <div class="review-item">
                    <dt>Target's Government Position</dt>
                    <dd id="reviewGovPosition"></dd>
                </div>

                <div class="review-item">
                    <dt>Target's Location</dt>
                    <dd id="reviewLocation"></dd>
                </div>
            </dl>

            <div class="modal-actions">
                <button
                    type="button"
                    class="cancel-btn"
                    id="cancelBtn"
                >
                    Cancel
                </button>

                <button
                    type="button"
                    class="confirm-btn"
                    id="confirmBtn"
                >
                    Confirm &amp; Send
                </button>
            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById("wishForm");
        const modal = document.getElementById("verifyModal");
        const cancelBtn = document.getElementById("cancelBtn");
        const confirmBtn = document.getElementById("confirmBtn");

        // Intercept normal submission and display the verification modal.
        form.addEventListener("submit", function (e) {
            e.preventDefault();

            // Run the browser's required-field and maxlength validation.
            if (!form.reportValidity()) {
                return;
            }

            // textContent safely displays user-entered values.
            document.getElementById("reviewFirstName").textContent =
                document.getElementById("FirstName").value || "(Not provided)";

            document.getElementById("reviewLastName").textContent =
                document.getElementById("LastName").value || "(Not provided)";

            document.getElementById("reviewGovPosition").textContent =
                document.getElementById("GovPosition").value || "(Not provided)";

            document.getElementById("reviewLocation").textContent =
                document.getElementById("Location").value || "(Not provided)";

            modal.classList.add("active");
            cancelBtn.focus();
        });

        // Close the modal without submitting the form.
        cancelBtn.addEventListener("click", function () {
            modal.classList.remove("active");
        });

        // Submit directly, bypassing the submit event listener.
        confirmBtn.addEventListener("click", function () {
            if (form.reportValidity()) {
                form.submit();
            }
        });

        // Close the modal when the user clicks outside the dialog.
        modal.addEventListener("click", function (e) {
            if (e.target === modal) {
                modal.classList.remove("active");
            }
        });

        // Allow Escape to close the modal.
        document.addEventListener("keydown", function (e) {
            if (e.key === "Escape" && modal.classList.contains("active")) {
                modal.classList.remove("active");
            }
        });
    </script>

</body>
</html>
