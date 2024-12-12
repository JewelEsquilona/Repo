<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in; redirect to login page if not
if (!isset($_SESSION['user_role'])) {
    header("Location: ../index.php");
    exit;
}

// Connection file
$connectionFile = '../connection.php';
if (!file_exists($connectionFile)) {
    die("Connection file not found.");
}
include($connectionFile);

if (!$con) {
    die("Database connection failed: " . $con->errorInfo()[2]);
}

// Get the user's email from the session
$user_email = $_SESSION['user_email'] ?? null;

if ($user_email === null) {
    die("User email not found in session. Please log in.");
}

// Fetch alumni data with JOIN
try {
    $query = "
        SELECT 
            a.*, 
            e.Employment, 
            e.Employment_Status, 
            e.Present_Occupation, 
            e.Name_of_Employer, 
            e.Address_of_Employer, 
            e.Number_of_Years_in_Present_Employer, 
            e.Type_of_Employer, 
            e.Major_Line_of_Business,
            CONCAT('AL', LPAD(a.Alumni_ID_Number, 5, '0')) AS Alumni_ID_Number_Format
        FROM `2024-2025` a
        LEFT JOIN `2024-2025_ed` e 
            ON a.`Alumni_ID_Number` = e.`Alumni_ID_Number`
        WHERE a.Personal_Email = :email
    ";
    $stmt = $con->prepare($query);
    $stmt->bindParam(':email', $user_email);
    $stmt->execute();
} catch (PDOException $e) {
    die("Error fetching alumni data: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni Profile</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body class="bg-content">
<div class="container-fluid px">
    <?php include "component/nav.php"; ?>

    <div class="alumni-list-header text-center py-2">
        <div class="title h3 fw-bold">Your Alumni Profile</div>
    </div>

    <!-- Alumni Profile Information -->
    <div class="profile-container">
        <?php if ($stmt->rowCount() > 0): ?>
            <?php $row = $stmt->fetch(PDO::FETCH_ASSOC); ?>
            <div class="card mb-4">
                <div class="card-body">
                    <p><strong>Student Number:</strong> <?= htmlspecialchars($row['Student_Number'] ?? 'N/A') ?></p>
                    <p><strong>Last Name:</strong> <?= htmlspecialchars($row['Last_Name'] ?? 'N/A') ?></p>
                    <p><strong>First Name:</strong> <?= htmlspecialchars($row['First_Name'] ?? 'N/A') ?></p>
                    <p><strong>Middle Name:</strong> <?= htmlspecialchars($row['Middle_Name'] ?? 'N/A') ?></p>
                    <p><strong>College:</strong> <?= htmlspecialchars($row['College'] ?? 'N/A') ?></p>
                    <p><strong>Department:</strong> <?= htmlspecialchars($row['Department'] ?? 'N/A') ?></p>
                    <p><strong>Section:</strong> <?= htmlspecialchars($row['Section'] ?? 'N/A') ?></p>
                    <p><strong>Year Graduated:</strong> <?= htmlspecialchars($row['Year_Graduated'] ?? 'N/A') ?></p>
                    <p><strong>Contact Number:</strong> <?= htmlspecialchars($row['Contact_Number'] ?? 'N/A') ?></p>
                    <p><strong>Personal Email:</strong> <?= htmlspecialchars($row['Personal_Email'] ?? 'N/A') ?></p>
                    <p><strong>Employment:</strong> <?= htmlspecialchars($row['Employment'] ?? 'N/A') ?></p>
                    <p><strong>Employment Status:</strong> <?= htmlspecialchars($row['Employment_Status'] ?? 'N/A') ?></p>
                    <p><strong>Present Occupation:</strong> <?= htmlspecialchars($row['Present_Occupation'] ?? 'N/A') ?></p>
                    <p><strong>Name of Employer:</strong> <?= htmlspecialchars($row['Name_of_Employer'] ?? 'N/A') ?></p>
                    <p><strong>Address of Employer:</strong> <?= htmlspecialchars($row['Address_of_Employer'] ?? 'N/A') ?></p>
                    <p><strong>Number of Years in Present Employer:</strong> <?= htmlspecialchars($row['Number_of_Years_in_Present_Employer'] ?? 'N/A') ?></p>
                    <p><strong>Type of Employer:</strong> <?= htmlspecialchars($row['Type_of_Employer'] ?? 'N/A') ?></p>
                    <p><strong>Major Line of Business:</strong> <?= htmlspecialchars($row['Major_Line_of_Business'] ?? 'N/A') ?></p>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning text-center" role="alert">
                No alumni records found.
            </div>
        <?php endif; ?>
    </div>
</div>
<script src="../assets/js/bootstrap.bundle.js"></script>
</body>
</html>
