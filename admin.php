<?php
session_start();
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    die("❌ Geen toegang.");
}

$host = 'localhost';
$db = 'bitcoin_forum';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);

$result = $conn->query("
    SELECT threads.id, threads.title, users.username 
    FROM threads 
    JOIN users ON threads.user_id = users.id 
    ORDER BY threads.created_at DESC
");

if (isset($_GET['delete'])) {
    $thread_id = intval($_GET['delete']);
    $conn->query("DELETE FROM posts WHERE thread_id = $thread_id");
    $conn->query("DELETE FROM threads WHERE id = $thread_id");
    header("Location: admin.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>🛠️ Admin Panel</h1>
        <ul>
            <?php while ($row = $result->fetch_assoc()): ?>
                <li class="topic">
                    <b><?php echo htmlspecialchars($row['title']); ?></b> van <?php echo htmlspecialchars($row['username']); ?>
                    - <a href="?delete=<?php echo $row['id']; ?>" class="delete" onclick="return confirm('Verwijderen?')">🗑️ Verwijder</a>
                </li>
            <?php endwhile; ?>
        </ul>
        <p><a href="forum.php">⬅️ Terug naar forum</a></p>
    </div>
</body>
</html>