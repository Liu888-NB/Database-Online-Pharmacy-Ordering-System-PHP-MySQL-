<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PharmacyPlatform</title>
    <link rel="icon" href="qiao_logo.svg" type="image/x-icon">
    <link rel="stylesheet" type="text/css" href="styles.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-image: url('back_picture.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed; 
            margin: 0; 
            height: 100vh;
        }
        .main-container {
            display: flex;
            position: relative;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-container {
            width: 30%;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
        }
        .login-container h2 {
            margin-top: 0;
            text-align: center;
        }
        .login-container form {
            text-align: center;
        }
        .login-container input[type="text"],
        .login-container input[type="password"],
        .login-container button {
            width: 100%;
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .login-container button {
            background-color: #007bff;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        .login-container button:focus {
            border-color: #0056b3;
            box-shadow: 0 0 5px rgba(0, 0, 255, 0.5);
        }
        .links {
            color: #00aeec;
        }
        .links:hover {
            color: #00aeec;
        }
        .links:visited {
            color: #00aeec;
        }
        .warning {
            color: red;
            font-size: 0.9em;
            display: none;
        }
    </style>
</head>
<body>
<div class="main-container">
    <div class="login-container">
        <img src="qiao_logo.svg" style="width: 300px;">
        <h1 style="font-family: 'Segoe UI Light', Arial, sans-serif; padding: 0px; margin: 0px; color: #aaa;" id="greetings"></h1>
        <h2 style="margin: 20px;">Login</h2>
        <form id="loginForm" action="logincheck_combine.php" method="post">
            <select name="role" id="role" required style="width: 100%; padding: 10px; margin-bottom: 10px;">
                <option value="user">User</option>
                <option value="pharmacy">Pharmacy</option>
            </select>
            <input type="text" name="username" placeholder="Username" id="username" required>
            <input type="password" name="password" placeholder="Password" id="password" required>
            <div id="warning" class="warning">Invalid username or password!</div>
            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php" class="links">Register</a></p>
    </div>
</div>

<script>
    var currentDate = new Date();
    var currentHour = currentDate.getHours();
    var body = document.body;
    if (currentHour < 4 || currentHour > 19) {
        body.style.backgroundImage = "url('back_picture.png')";
        document.getElementById("greetings").innerHTML = "Good evening! For your health, don't stay up late.";
    } else if (currentHour >= 4 && currentHour < 7) {
        body.style.backgroundImage = "url('back_picture.png')";
        document.getElementById("greetings").innerHTML = "Good morning! Ready to take care of your health today?";
    } else if (currentHour >= 7 && currentHour < 16) {
        body.style.backgroundImage = "url('back_picture.png')";
        document.getElementById("greetings").innerHTML = "Good day! We're here to help you with your healthcare needs.";
    } else {
        body.style.backgroundImage = "url('back_picture.png')";
        document.getElementById("greetings").innerHTML = "Good evening! Remember, health is wealth. Take a moment to relax.";
    }

    function inputFocus(input) {
        input.style.backgroundColor = "white";
        input.style.borderColor = "#00AEEC";
    }

    // Form validation
    document.getElementById('loginForm').onsubmit = function(event) {
        var username = document.getElementById('username').value;
        var password = document.getElementById('password').value;
        var warning = document.getElementById('warning');

        if (username === "" || password === "") {
            warning.innerText = "Username and password cannot be empty!";
            warning.style.display = 'block';  // for warning 
            event.preventDefault(); // stop the submission
        } else {
            warning.style.display = 'none';  // hiden warning message
        }
    };
</script>

</body>
</html>
