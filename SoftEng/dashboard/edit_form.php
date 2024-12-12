<?php
session_start();
include '../connection.php';
include 'user_privileges.php';

// Check if user is logged in and has the 'Alumni' role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Alumni') {
    header('Location: ../index.php');
    exit();
}

// Get the user's email from the session
$user_email = $_SESSION['user_email'] ?? null;

if ($user_email === null) {
    die("User email not found in session. Please log in.");
}

// Fetch alumni data based on the personal email
try {
    $query = "SELECT * FROM `2024-2025` WHERE Personal_Email = :email";
    $stmt = $con->prepare($query);
    $stmt->bindParam(':email', $user_email);
    $stmt->execute();

    // Check if alumni data was retrieved successfully
    if ($stmt->rowCount() > 0) {
        $alumniData = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        die("Error fetching alumni data. No records found.");
    }
} catch (PDOException $e) {
    die("Error fetching alumni data: " . $e->getMessage());
}

$collegesQuery = "SELECT DISTINCT college FROM courses";
$collegesStmt = $con->prepare($collegesQuery);
$collegesStmt->execute();
$existingColleges = $collegesStmt->fetchAll(PDO::FETCH_COLUMN);

// Fetch employment data
try {
    $employmentQuery = "SELECT * FROM `2024-2025_ed` WHERE Alumni_ID_Number = :alumni_id";
    $stmtEd = $con->prepare($employmentQuery);
    $stmtEd->bindParam(':alumni_id', $alumniData['Alumni_ID_Number']);
    $stmtEd->execute();

    // Check if employment data was retrieved successfully
    if ($stmtEd->rowCount() > 0) {
        $edData = $stmtEd->fetch(PDO::FETCH_ASSOC);
    } else {
        $edData = []; // No employment data found
    }
} catch (PDOException $e) {
    die("Error fetching employment data: " . $e->getMessage());
}

// Initialize success message variable
$successMessage = "";

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $studentNumber = $_POST['student_number'];
    $lastName = $_POST['last_name'];
    $firstName = $_POST['first_name'];
    $middleName = $_POST['middle_name'];
    $college = $_POST['college'];
    $department = $_POST['department'];
    $section = $_POST['section'];
    $yearGraduated = $_POST['year_graduated'];
    $contactNumber = $_POST['contact_number'];
    $personalEmail = $_POST['personal_email'];
    $employment = $_POST['employment'];
    $employmentStatus = $_POST['employment_status'];
    $presentOccupation = $_POST['present_occupation'];
    $nameOfEmployer = $_POST['name_of_employer'];
    $addressOfEmployer = $_POST['address_of_employer'];
    $numberOfYearsInPresentEmployer = $_POST['number_of_years_in_present_employer'];
    $typeOfEmployer = $_POST['type_of_employer'];
    $majorLineOfBusiness = $_POST['major_line_of_business'];

    // Update the 2024-2025 table
    $updateQuery = "UPDATE `2024-2025` SET
        Student_Number = :student_number,
        Last_Name = :last_name,
        First_Name = :first_name,
        Middle_Name = :middle_name,
        College = :college,
        Department = :department,
        Section = :section,
        Year_Graduated = :year_graduated,
        Contact_Number = :contact_number,
        Personal_Email = :personal_email
    WHERE Alumni_ID_Number = :alumni_id";

    $updateStmt = $con->prepare($updateQuery);
    $updateStmt->bindParam(':student_number', $studentNumber);
    $updateStmt->bindParam(':last_name', $lastName);
    $updateStmt->bindParam(':first_name', $firstName);
    $updateStmt->bindParam(':middle_name', $middleName);
    $updateStmt->bindParam(':college', $college);
    $updateStmt->bindParam(':department', $department);
    $updateStmt->bindParam(':section', $section);
    $updateStmt->bindParam(':year_graduated', $yearGraduated);
    $updateStmt->bindParam(':contact_number', $contactNumber);
    $updateStmt->bindParam(':personal_email', $personalEmail);
    $updateStmt->bindParam(':alumni_id', $alumniData['Alumni_ID_Number']);

    if ($updateStmt->execute()) {
        // Update the 2024-2025_ed table
        $updateEdQuery = "UPDATE `2024-2025_ed` SET
            Employment = :employment,
            Employment_Status = :employment_status,
            Present_Occupation = :present_occupation,
            Name_of_Employer = :name_of_employer,
            Address_of_Employer = :address_of_employer,
            Number_of_Years_in_Present_Employer = :number_of_years_in_present_employer,
            Type_of_Employer = :type_of_employer,
            Major_Line_of_Business = :major_line_of_business
        WHERE Alumni_ID_Number = :alumni_id";

        $updateEdStmt = $con->prepare($updateEdQuery);
        $updateEdStmt->bindParam(':employment', $employment);
        $updateEdStmt->bindParam(':employment_status', $employmentStatus);
        $updateEdStmt->bindParam(':present_occupation', $presentOccupation);
        $updateEdStmt->bindParam(':name_of_employer', $nameOfEmployer);
        $updateEdStmt->bindParam(':address_of_employer', $addressOfEmployer);
        $updateEdStmt->bindParam(':number_of_years_in_present_employer', $numberOfYearsInPresentEmployer);
        $updateEdStmt->bindParam(':type_of_employer', $typeOfEmployer);
        $updateEdStmt->bindParam(':major_line_of_business', $majorLineOfBusiness);
        $updateEdStmt->bindParam(':alumni_id', $alumniData['Alumni_ID_Number']);

        if ($updateEdStmt->execute()) {
            $successMessage = "Your Alumni Data is successfully updated!";
        } else {
            echo "<script>alert('Error updating alumni data. Please try again.');</script>";
        }
    } else {
        echo "<script>alert('Error updating alumni data. Please try again.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Alumni</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.css">
    <link rel="stylesheet" href="../assets/css/reg.css">
</head>

<body>
    <div class="container-fluid px">
        <?php include "component/nav.php"; ?>
        <div class="container form-container mt-7">
            <header class="mb-4">Edit Alumni</header>

            <?php if ($successMessage): ?>
                <div class="alert alert-success" role="alert">
                    <?= htmlspecialchars($successMessage) ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="form">
                <div class="mb-3">
                    <label for="student-number" class="form-label">Student Number</label>
                    <input type="text" id="student-number" name="student_number" class="form-control" value="<?= htmlspecialchars($alumniData['Student_Number'] ?? '') ?>" required>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" value="<?= htmlspecialchars($alumniData['Last_Name'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" value="<?= htmlspecialchars($alumniData['First_Name'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="middle_name" class="form-label">Middle Name</label>
                        <input type="text" class="form-control" id="middle_name" name="middle_name" value="<?= htmlspecialchars($alumniData['Middle_Name'] ?? '') ?>">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="college" class="form-label">College</label>
                        <select class="form-control" id="college" name="college" required onchange="updateDepartments()">
                            <option value="">Select College</option>
                            <?php foreach ($existingColleges as $college): ?>
                                <option value="<?= htmlspecialchars($college) ?>" <?= (isset($alumniData['College']) && $alumniData['College'] == $college) ? 'selected' : ''; ?>><?= htmlspecialchars($college) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="department" class="form-label">Department</label>
                        <select class="form-control" id="department" name="department" required onchange="updateSections()">
                            <option value="">Select Department</option>
                            <!-- Populate this dynamically -->
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="section" class="form-label">Section</label>
                        <select class="form-control" id="section" name="section" required>
                            <option value="">Select Section</option>
                            <!-- Populate this dynamically -->
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="year_graduated" class="form-label">Year Graduated</label>
                        <input type="text" class="form-control" id="year_graduated" name="year_graduated" value="<?= htmlspecialchars($alumniData['Year_Graduated'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="contact_number" class="form-label">Contact Number</label>
                        <input type="text" class="form-control" id="contact_number" name="contact_number" value="<?= htmlspecialchars($alumniData['Contact_Number'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="personal_email" class="form-label">Personal Email</label>
                        <input type="email" class="form-control" id="personal_email" name="personal_email" value="<?= htmlspecialchars($alumniData['Personal_Email'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="mb-3 row align-items-center">
                    <div class="col-md-6">
                        <label for="employment" class="form-label">Employment</label>
                        <select id="employment" name="employment" class="form-control" required onchange="toggleEmploymentFields()">
                            <option value="">Select Employment</option>
                            <option value="Employed" <?= isset($edData['Employment']) && $edData['Employment'] === 'Employed' ? 'selected' : '' ?>>Employed</option>
                            <option value="Self-employed" <?= isset($edData['Employment']) && $edData['Employment'] === 'Self-employed' ? 'selected' : '' ?>>Self-employed</option>
                            <option value="Actively looking for a job" <?= isset($edData['Employment']) && $edData['Employment'] === 'Actively looking for a job' ? 'selected' : '' ?>>Actively Looking for a Job</option>
                            <option value="Never been employed" <?= isset($edData['Employment']) && $edData['Employment'] === 'Never been employed' ? 'selected' : '' ?>>Never Been Employed</option>
                        </select>
                    </div>
                    <div class="col-md-6" id="employment-status-container" style="display: <?= isset($edData['Employment']) && $edData['Employment'] === 'Employed' ? 'block' : 'none' ?>;">
                        <label for="employment_status" class="form-label">Employment Status</label>
                        <select id="employment_status" name="employment_status" class="form-control" required>
                            <option value="">Select Employment Status</option>
                            <option value="Regular/Permanent" <?= isset($edData['Employment_Status']) && $edData['Employment_Status'] === 'Regular/Permanent' ? 'selected' : '' ?>>Regular/Permanent</option>
                            <option value="Casual" <?= isset($edData['Employment_Status']) && $edData['Employment_Status'] === 'Casual' ? 'selected' : '' ?>>Casual</option>
                            <option value="Contractual" <?= isset($edData['Employment_Status']) && $edData['Employment_Status'] === 'Contractual' ? 'selected' : '' ?>>Contractual</option>
                            <option value="Temporary" <?= isset($edData['Employment_Status']) && $edData['Employment_Status'] === 'Temporary' ? 'selected' : '' ?>>Temporary</option>
                            <option value="Part-time (seeking full-time)" <?= isset($edData['Employment_Status']) && $edData['Employment_Status'] === 'Part-time (seeking full-time)' ? 'selected' : '' ?>>Part-time (seeking full-time)</option>
                            <option value="Part-time (but not seeking full-time)" <?= isset($edData['Employment_Status']) && $edData['Employment_Status'] === 'Part-time (but not seeking full-time)' ? 'selected' : '' ?>>Part-time (but not seeking full-time)</option>
                            <option value="Other" <?= isset($edData['Employment_Status']) && $edData['Employment_Status'] === 'Other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                </div>

                <div id="employmentFields" style="display: <?= isset($edData['Employment']) && $edData['Employment'] === 'Employed' ? 'block' : 'none' ?>;">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="present_occupation" class="form-label">Present Occupation</label>
                            <input type="text" class="form-control" id="present_occupation" name="present_occupation" value="<?= isset($edData['Present_Occupation']) ? htmlspecialchars($edData['Present_Occupation']) : '' ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="name_of_employer" class="form-label">Name of Employer</label>
                            <input type="text" class="form-control" id="name_of_employer" name="name_of_employer" value="<?= isset($edData['Name_of_Employer']) ? htmlspecialchars($edData['Name_of_Employer']) : '' ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="address_of_employer" class="form-label">Address of Employer</label>
                            <input type="text" class="form-control" id="address_of_employer" name="address_of_employer" value="<?= isset($edData['Address_of_Employer']) ? htmlspecialchars($edData['Address_of_Employer']) : '' ?>">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="number_of_years_in_present_employer" class="form-label">Years in Present Employer</label>
                            <input type="number" class="form-control" id="number_of_years_in_present_employer" name="number_of_years_in_present_employer" value="<?= isset($edData['Number_of_Years_in_Present_Employer']) ? htmlspecialchars($edData['Number_of_Years_in_Present_Employer']) : '' ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="type_of_employer" class="form-label">Type of Employer</label>
                            <input type="text" class="form-control" id="type_of_employer" name="type_of_employer" value="<?= isset($edData['Type_of_Employer']) ? htmlspecialchars($edData['Type_of_Employer']) : '' ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="major_line_of_business" class="form-label">Major Line of Business</label>
                            <input type="text" class="form-control" id="major_line_of_business" name="major_line_of_business" value="<?= isset($edData['Major_Line_of_Business']) ? htmlspecialchars($edData['Major_Line_of_Business']) : '' ?>">
                        </div>
                    </div>
                </div>

                <div class="button-container text-end mt-4">
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const collegeSelect = document.getElementById('college');
                const departmentSelect = document.getElementById('department');
                const sectionSelect = document.getElementById('section');

                updateDepartments(collegeSelect.value, "<?= htmlspecialchars($alumniData['Department'] ?? '') ?>", "<?= htmlspecialchars($alumniData['Section'] ?? '') ?>");

                collegeSelect.addEventListener('change', function() {
                    updateDepartments(this.value, '', '');
                });

                departmentSelect.addEventListener('change', function() {
                    updateSections(this.value, '');
                });
            });

            function updateDepartments(college, selectedDepartment, selectedSection) {
                const departmentSelect = document.getElementById('department');
                const sectionSelect = document.getElementById('section');

                departmentSelect.innerHTML = '<option value="">Select Department</option>';
                sectionSelect.innerHTML = '<option value="">Select Section</option>';

                // Example data (you would replace this with your actual data)
                const departments = {
                    'College of Engineering': ['Computer Engineering', 'Civil Engineering'],
                    'College of Arts': ['Fine Arts', 'Performing Arts'],
                    // Add other colleges and their departments here
                };

                if (departments[college]) {
                    departments[college].forEach(department => {
                        const option = document.createElement('option');
                        option.value = department;
                        option.textContent = department;
                        if (department === selectedDepartment) {
                            option.selected = true;
                        }
                        departmentSelect.appendChild(option);
                    });
                }

                updateSections(departmentSelect.value, selectedSection);
            }

            function updateSections(department, selectedSection) {
                const sectionSelect = document.getElementById('section');
                sectionSelect.innerHTML = '<option value="">Select Section</option>';

                // Example data (you would replace this with your actual data)
                const sections = {
                    'Computer Engineering': ['CE1', 'CE2'],
                    'Civil Engineering': ['CIV1', 'CIV2'],
                    'Fine Arts': ['FA1', 'FA2'],
                    'Performing Arts': ['PA1', 'PA2'],
                    // Add other departments and their sections here
                };

                if (sections[department]) {
                    sections[department].forEach(section => {
                        const option = document.createElement('option');
                        option.value = section;
                        option.textContent = section;
                        if (section === selectedSection) {
                            option.selected = true;
                        }
                        sectionSelect.appendChild(option);
                    });
                }
            }

            function toggleEmploymentFields() {
                const employmentSelect = document.getElementById('employment');
                const employmentFields = document.getElementById('employmentFields');
                const employmentStatusContainer = document.getElementById('employment-status-container');

                if (employmentSelect.value === 'Employed') {
                    employmentFields.style.display = 'block';
                    employmentStatusContainer.style.display = 'block';
                } else {
                    employmentFields.style.display = 'none';
                    employmentStatusContainer.style.display = 'none';
                }
            }

            // Initialize the display of employment fields based on the current employment status
            toggleEmploymentFields();
        </script>
    </div>
</body>

</html>