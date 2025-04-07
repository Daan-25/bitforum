<?php
$host = 'localhost';
$db = 'bitcoin_forum';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $password);

    if ($stmt->execute()) {
        echo "✅ Registratie gelukt. <a href='index.php'>Log nu in</a>";
    } else {
        echo "❌ Gebruikersnaam bestaat al of fout in query.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registreren - Bitcoin Forum</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>₿ Registreren</h1>
        <form method="post" class="form">
            <label>Gebruikersnaam:</label><br>
            <input type="text" name="username" required><br><br>
            <label>Wachtwoord:</label><br>
            <input type="password" name="password" required><br><br>
            <button type="submit">Registreer</button>
        </form>
    </div>
</body>
</html>