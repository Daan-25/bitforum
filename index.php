<?php
session_start();

$host = 'localhost';
$db = 'bitcoin_forum';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Verbinding mislukt: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, password, is_admin FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $db_username, $db_password, $is_admin);
        $stmt->fetch();

        if ($is_admin == 1 && empty($password)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $db_username;
            $_SESSION['is_admin'] = $is_admin;
            header("Location: admin.php");
            exit;
        }
        
        if (password_verify($password, $db_password)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $db_username;
            $_SESSION['is_admin'] = $is_admin;
            header("Location: forum.php");
            exit;
        } else {
            $error_message = "❌ Onjuist wachtwoord.";
        }
    } else {
        $error_message = "❌ Gebruiker niet gevonden.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Login - Bitcoin Forum</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>₿ Bitcoin Forum Login</h2>
        <form method="post" class="form">
            <label>Gebruikersnaam:</label> <input type="text" name="username" required><br>
            <label>Wachtwoord:</label> <input type="password" name="password"><br>
            <button type="submit">Inloggen</button>
        </form>
        <?php if (isset($error_message)) echo "<p>$error_message</p>"; ?>
        <footer>Geen account? <a href="register.php">Registreer hier</a></footer>
    </div>
</body>
</html>