<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require 'includes/db.php';

$user_id = $_SESSION['user_id'];

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_image'])) {
    $image_name = $_FILES['profile_image']['name'];
    $image_tmp_name = $_FILES['profile_image']['tmp_name'];
    $image_size = $_FILES['profile_image']['size'];
    $image_error = $_FILES['profile_image']['error'];

    $image_ext = pathinfo($image_name, PATHINFO_EXTENSION);
    $allowed_ext = array('jpg', 'jpeg', 'png', 'gif');

    if (in_array($image_ext, $allowed_ext)) {
        if ($image_error === 0) {
            if ($image_size < 5000000) { // 5MB limit
                $new_image_name = uniqid('', true) . '.' . $image_ext;
                $image_destination = 'images/' . $new_image_name;

                if (move_uploaded_file($image_tmp_name, $image_destination)) {
                    // Insert image record into the database
                    $sql = "INSERT INTO images (user_id, image_path) VALUES (?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("is", $user_id, $new_image_name);
                    $stmt->execute();

                    // Redirect to avoid resubmission
                    header('Location: dashboard.php');
                    exit;
                } else {
                    $error_message = "Failed to upload image.";
                }
            } else {
                $error_message = "Image size is too big.";
            }
        } else {
            $error_message = "Error uploading image.";
        }
    } else {
        $error_message = "Invalid image format.";
    }
}

// Fetch the user's details
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Fetch the latest uploaded image
$sql = "SELECT * FROM images WHERE user_id = ? ORDER BY uploaded_at DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$image = $result->fetch_assoc();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">

    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        /* Navbar Styles */
        .navbar {
            background-color: #333;
            color: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 20px;
        }
        .navbar .logo {
            font-size: 1.5rem;
            font-weight: bold;
        }
        /* Aside Navbar Styles */
        .aside-navbar {
            background-color: #f4f4f4;
            width: 150px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .aside-navbar .profile-image {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 20px;
        }
        .aside-navbar .profile-image img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin-bottom: 10px;
        }
        .aside-navbar .upload-icon {
            font-size: 24px;
            margin-bottom: 10px;
            cursor: pointer;
            color: blue;
        }
        .aside-navbar .upload-icon:hover {
            text-decoration: underline;
        }
        .aside-navbar .profile-name {
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }
        .aside-navbar .logout-btn {
            background-color: #333;
            color: #fff;
            text-decoration: none;
            text-align: center;
            padding: 10px 0;
            border-radius: 5px;
            margin-bottom: 80px;
            margin-top: auto;
        }
        /* Main Content Styles */
        .main-content {
            margin-left: 220px; /* adjust according to aside width */
            padding: 20px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <div class="logo">Portfolio</div>
        <div class="user-info">Welcome, <?php echo htmlspecialchars($user['username']); ?></div>
    </div>

    <!-- Aside Navbar -->
    <div class="aside-navbar">
        <div class="profile-image">
            <?php if ($image): ?>
                <img src="images/<?php echo htmlspecialchars($image['image_path']); ?>" alt="Profile Image">
            <?php else: ?>
                <img src="profile.jpg" alt="Profile Image">
            <?php endif; ?>
            <?php if (isset($error_message)): ?>
                <p style="color: red;"><?php echo htmlspecialchars($error_message); ?></p>
            <?php endif; ?>
            <div class="upload-icon" onclick="document.getElementById('profile_image').click()"><i class="fa-solid fa-upload"></i></div>
            <form id="uploadForm" action="dashboard.php" method="post" enctype="multipart/form-data">
                <input type="file" name="profile_image" id="profile_image" style="display: none;">
                <input type="submit" value="Upload" style="display: none;">
            </form>
        </div>
        <div class="profile-name"><?php echo htmlspecialchars($user['username']); ?></div>
        <div class="logout-btn" id="logout-btn">Logout</div>
        <!-- <a href="logout.php" class="logout-btn" id="logout-btn">Logout</a> -->
    </div>


    <script>
        document.getElementById('profile_image').addEventListener('change', function() {
            document.getElementById('uploadForm').submit();
        });

                // Add an event listener to the logout button
                document.getElementById('logout-btn').addEventListener('click', function() {
            // Make an AJAX request to the logout script
            fetch('logout.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'logged_out') {
                        // Broadcast logout event to other tabs
                        localStorage.setItem('logout', Date.now());
                        // Show alert message
                        alert('You have been logged out.');
                        // Redirect to index page
                        window.location.href = 'index.html';
                    }
                });
        });

        // Listen for the storage event to handle logout from other tabs
        window.addEventListener('storage', function(event) {
            if (event.key === 'logout') {
                // Show alert message
                alert('You have been logged out.');
                // Redirect to index page
                window.location.href = 'index.html';
            }
        });


        /*Session timeout */
// Set the session timeout duration (60 seconds)
const sessionTimeoutDuration = 30000; // in milliseconds

let timeout;

// Function to reset the session timeout
function resetSessionTimeout() {
    clearTimeout(timeout);
    timeout = setTimeout(logoutUser, sessionTimeoutDuration);
}

// Function to logout the user
// Function to logout the user
function logoutUser() {
    // Make an AJAX request to the logout script
    fetch('logout.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'logged_out') {
                // Broadcast logout event to other tabs
                localStorage.setItem('logout', Date.now());
                // Redirect to index page with a query parameter indicating logout
                window.location.href = 'index.html?logout=true';
            }
        });
}

// Reset the session timeout on user activity
document.addEventListener('mousemove', resetSessionTimeout);
document.addEventListener('keypress', resetSessionTimeout);

// Initial setup of session timeout
resetSessionTimeout();

// Listen for the storage event to handle logout from other tabs
window.addEventListener('storage', function(event) {
    if (event.key === 'logout') {
        // Redirect to index page
        window.location.href = 'index.html';
    }
    alert('You have been logged out due to inactivity.');

});


    </script>
</body>
</html>