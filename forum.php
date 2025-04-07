<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$host = 'localhost';
$db = 'bitcoin_forum';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);

// Voeg nieuw topic toe
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['title'])) {
    $title = trim($_POST['title']);
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO threads (title, user_id) VALUES (?, ?)");
    $stmt->bind_param("si", $title, $user_id);
    $stmt->execute();
    $stmt->close();
}

// Verwijder thread
if (isset($_GET['delete_thread'])) {
    $thread_id = intval($_GET['delete_thread']);
    $stmt = $conn->prepare("SELECT user_id FROM threads WHERE id = ?");
    $stmt->bind_param("i", $thread_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $thread = $result->fetch_assoc();
    
    if ($thread && $thread['user_id'] == $_SESSION['user_id']) {
        $conn->query("DELETE FROM posts WHERE thread_id = $thread_id");
        $conn->query("DELETE FROM threads WHERE id = $thread_id");
    }
    $stmt->close();
    header("Location: forum.php");
    exit;
}

// Haal alle threads op
$result = $conn->query("
    SELECT threads.id, threads.title, threads.created_at, threads.user_id, users.username 
    FROM threads 
    JOIN users ON threads.user_id = users.id 
    ORDER BY threads.created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Bitcoin Forum</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>₿ Welkom op het Bitcoin Forum, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <form method="post" class="form">
            <input type="text" name="title" placeholder="Nieuw topic titel" required>
            <button type="submit">Plaats topic</button>
        </form>
        <h2>Topics</h2>
        <ul>
            <?php while ($row = $result->fetch_assoc()): ?>
                <li class="topic">
                    <a href="thread.php?id=<?php echo $row['id']; ?>">
                        <?php echo htmlspecialchars($row['title']); ?>
                    </a> 
                    - geplaatst door <b><?php echo htmlspecialchars($row['username']); ?></b> op 
                    <?php echo $row['created_at']; ?>
                    <?php if ($row['user_id'] == $_SESSION['user_id']): ?>
                        - <a href="?delete_thread=<?php echo $row['id']; ?>" class="delete" onclick="return confirm('Dit topic en alle reacties verwijderen?')">🗑️ Verwijder</a>
                    <?php endif; ?>
                </li>
            <?php endwhile; ?>
        </ul>
        <p><a href="logout.php">Log uit</a></p>
    </div>
</body>
</html>