<?php
include 'session.php';
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
}

if (isset($_GET['id'])) {
    $stmt = $mysqli->prepare("SELECT * FROM files WHERE id = ?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $file = $result->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $file_upload = $_FILES['file'];
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($file_upload["name"]);

    if (move_uploaded_file($file_upload["tmp_name"], $target_file)) {
        $stmt = $mysqli->prepare("UPDATE files SET filename = ?, filepath = ? WHERE id = ?");
        $stmt->bind_param("ssi", $file_upload["name"], $target_file, $file['id']);
        $stmt->execute();
        $stmt->close();
        header("Location: dashboard.php");
    }
}

include 'header.php';
?>

<h1>Edit File: <?= $file['filename'] ?></h1>
<form method="POST" enctype="multipart/form-data">
    <div class="form-group">
        <input type="file" class="form-control" name="file" required>
    </div>
    <button type="submit" class="btn btn-primary">Save Changes</button>
</form>

<?php include 'footer.php'; ?>
