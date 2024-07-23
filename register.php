<?php
include 'includes/db.php'; // Include your database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate username: Only characters are allowed
    if (!preg_match('/^[a-zA-Z]+$/', $username)) {
        echo json_encode(array("error" => "Username can only contain letters."));
        exit;
    }

    // Validate email: Only allow format varchar@gmail.com
    if (!preg_match('/^[a-zA-Z]+@gmail\.com$/', $email)) {
        echo json_encode(array("error" => "Email must be end with '@gmail.com'."));
        exit;
    }

    // Validate password
    if (strlen($password) < 8 || !preg_match('/[@&$#]/', $password)) {
        echo json_encode(array("error" => "Password must be at least 8 characters long and contain one of the symbols @&$#." ));
        exit;
    }

    if ($password !== $confirm_password) {
        echo json_encode(array("error" => "Passwords do not match."));
        exit;
    }

    // Encrypt the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Perform database operation: Insert user data into the database
    $sql = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$hashed_password')";
    if ($conn->query($sql) === TRUE) {
        echo json_encode(array("success" => "Registration successful. You can now log in."));
    } else {
        echo json_encode(array("error" => "Error: " . $sql . "<br>" . $conn->error));
    }

    // Close the database connection
    $conn->close();
}
?>
