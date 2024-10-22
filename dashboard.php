<?php
include 'includes/session.php';
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($file["name"]);
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        $stmt = $mysqli->prepare("INSERT INTO files (user_id, filename, filepath) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $_SESSION['user_id'], $file["name"], $target_file);
        $stmt->execute();
        $stmt->close();
    }
}

include 'templates/header.php';

$stmt = $mysqli->prepare("SELECT * FROM files WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
?>

<h1>Your Dashboard</h1>
<form method="POST" enctype="multipart/form-data">
    <div class="form-group">
        <input type="file" class="form-control" name="file" required>
    </div>
    <button type="submit" class="btn btn-primary">Upload</button>
</form>

<h2>Your Files</h2>
<ul class="list-group">
    <?php while ($file = $result->fetch_assoc()): ?>
        <li class="list-group-item">
            <a href="edit.php?id=<?= $file['id'] ?>"><?= $file['filename'] ?></a>
        </li>
    <?php endwhile; ?>
</ul>

<?php include 'templates/footer.php'; ?>
