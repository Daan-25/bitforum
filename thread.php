<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

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

if ($conn->connect_error) {
    die("Verbinding mislukt: " . $conn->connect_error);
}

$thread_id = isset($_GET['id']) ? $_GET['id'] : null;
if (!$thread_id) {
    echo "❌ Geen thread geselecteerd.";
    exit;
}

// Voeg reactie toe
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['content'])) {
    $content = trim($_POST['content']);
    $user_id = $_SESSION['user_id'];
    $last_post_time = $_SESSION['last_post_time'] ?? 0;
    $now = time();
    $wait_seconds = 10;

    if ($now - $last_post_time < $wait_seconds) {
        die("❌ Je moet minstens $wait_seconds seconden wachten tussen reacties.");
    }
    if (strlen($content) < 5) {
        die("❌ Reactie is te kort.");
    }
    $_SESSION['last_post_time'] = $now;

    $stmt = $conn->prepare("INSERT INTO posts (thread_id, user_id, content) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $thread_id, $user_id, $content);
    $stmt->execute();
    $stmt->close();
}

// Verwijder post
if (isset($_GET['delete_post'])) {
    $post_id = intval($_GET['delete_post']);
    $stmt = $conn->prepare("SELECT user_id FROM posts WHERE id = ? AND thread_id = ?");
    $stmt->bind_param("ii", $post_id, $thread_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $post = $result->fetch_assoc();
    
    if ($post && $post['user_id'] == $_SESSION['user_id']) {
        $conn->query("DELETE FROM posts WHERE id = $post_id");
    }
    $stmt->close();
    header("Location: thread.php?id=$thread_id");
    exit;
}

// Haal thread info op
$thread_stmt = $conn->prepare("
    SELECT threads.title, users.username, threads.created_at 
    FROM threads 
    JOIN users ON threads.user_id = users.id 
    WHERE threads.id = ?
");
$thread_stmt->bind_param("i", $thread_id);
$thread_stmt->execute();
$thread_result = $thread_stmt->get_result();
$thread = $thread_result->fetch_assoc();
$thread_stmt->close();

// Haal alle reacties op
$posts_result = $conn->query("
    SELECT posts.id, posts.content, posts.created_at, posts.user_id, users.username 
    FROM posts 
    JOIN users ON posts.user_id = users.id 
    WHERE posts.thread_id = $thread_id 
    ORDER BY posts.created_at ASC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($thread['title']); ?> - Bitcoin Forum</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1><?php echo htmlspecialchars($thread['title']); ?></h1>
        <p>Gestart door <b><?php echo htmlspecialchars($thread['username']); ?></b> op <?php echo $thread['created_at']; ?></p>
        <h2>Reacties</h2>
        <?php while ($post = $posts_result->fetch_assoc()): ?>
            <div class="post">
                <p><b><?php echo htmlspecialchars($post['username']); ?></b> zei op <?php echo $post['created_at']; ?>:</p>
                <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                <?php if ($post['user_id'] == $_SESSION['user_id']): ?>
                    <p><a href="?id=<?php echo $thread_id; ?>&delete_post=<?php echo $post['id']; ?>" class="delete" onclick="return confirm('Deze reactie verwijderen?')">🗑️ Verwijder</a></p>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
        <h3>Voeg een reactie toe:</h3>
        <form method="post" class="form">
            <textarea name="content" rows="4" cols="50" required></textarea><br>
            <button type="submit">Plaats reactie</button>
        </form>
        <p><a href="forum.php">⬅️ Terug naar forum</a></p>
    </div>
</body>
</html>