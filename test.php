<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Popup Div</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .popup {
            display: none;
            position: fixed;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .popup-content {
            display: flex;
            position: relative;
            width: 450px;
            padding: 20px;
            background: url('images/popup.jpg') no-repeat center center;
            background-size: cover;
            text-align: center;
            color: #000;
        }

        .close {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 24px;
            font-weight: bold;
            color: #fff;
            cursor: pointer;
        }

        .btn-container {
            margin: 20px 10px;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
        }

        .btn {
            display: inline-block;
            margin: 10px;
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            outline: none;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-30px);
            }
            60% {
                transform: translateY(-15px);
            }
        }

        .bounce {
            animation: bounce 2s infinite;
        }
    </style>
</head>
<script>
     document.addEventListener('contextmenu', function(event) {
            event.preventDefault();
            alert('Right-click is disabled on this page.');
        });
</script>
<body>
    <div id="popup" class="popup">
        <div class="popup-content">
            <span class="close">&times;</span>
            <div class="btn-container">
                <p>If you're already a user, </p>
                <button class="btn bounce">Sign In</button>
            </div>
            <div class="btn-container">
                <p>If you're a new user,</p>
                <button class="btn bounce">Sign Up</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                document.getElementById('popup').style.display = 'flex';
            }, 5000);

            document.querySelector('.close').addEventListener('click', function() {
                document.getElementById('popup').style.display = 'none';
            });
        });
    </script>
</body>
</html>
