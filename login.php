<?php
include 'includes/db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $recaptchaResponse = $_POST['g-recaptcha-response'];

    // Verify reCAPTCHA response
    $secretKey = '6LcZLvMpAAAAAGs6ytZAQbh4WlkPn6B8T365vCdj';
    $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=$recaptchaResponse");
    $responseKeys = json_decode($response, true);

    if (intval($responseKeys["success"]) !== 1) {
        // ReCAPTCHA validation failed
        echo '<script>alert("Please complete the reCAPTCHA.");</script>';
        exit();
    }

    // Sanitize input to prevent SQL injection
    $email = mysqli_real_escape_string($conn, $email);

    // Fetch user details from the database
    $sql = "SELECT id, password, failed_attempts, lock_time FROM users WHERE LOWER(email) = LOWER('$email')";
    $result = $conn->query($sql);

    if ($result) {
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $stored_password = $row['password'];
            $failed_attempts = $row['failed_attempts'];
            $lock_time = $row['lock_time'];

            // Check if account is locked
            if ($lock_time && strtotime($lock_time) > time()) {
                $lock_duration = strtotime($lock_time) - time();
                echo '<script>alert("Account is locked. Please try again in ' . $lock_duration . ' seconds.");</script>';
                exit();
            }

            // Verify password
            if (password_verify($password, $stored_password)) {
                // Reset failed attempts and lock time on successful login
                $sql = "UPDATE users SET failed_attempts = 0, lock_time = NULL WHERE id = " . $row['id'];
                $conn->query($sql);

                // Store user ID in session for authentication
                $_SESSION['user_id'] = $row['id'];

                // Redirect to the dashboard or any other page
                echo '<script>
                    alert("Login successful");
                    window.location.href = "dashboard.php";
                    </script>';
                exit();
            } else {
                // Increment failed attempts and lock the account if necessary
                $failed_attempts++;
                if ($failed_attempts >= 3) {
                    $lock_time = date("Y-m-d H:i:s", strtotime("+30 seconds"));
                    $sql = "UPDATE users SET failed_attempts = $failed_attempts, lock_time = '$lock_time' WHERE id = " . $row['id'];
                    $conn->query($sql);
                    echo '<script>alert("Account locked due to too many failed attempts. Please try again in 30 seconds.");</script>';
                } else {
                    $sql = "UPDATE users SET failed_attempts = $failed_attempts WHERE id = " . $row['id'];
                    $conn->query($sql);
                    echo '<script>alert("Invalid Credentials. Attempt ' . $failed_attempts . ' of 3.");</script>';
                }
                exit();
            }
        } else {
            // No user found with the provided email
            echo '<script>alert("No user found with that email.");</script>';
            exit();
        }
    } else {
        // Database query error
        echo '<script>alert("Error: ' . $sql . ' - ' . $conn->error . '");</script>';
        exit();
    }

    $conn->close();
}
?>
