<?php
include 'includes/session.php';
include 'includes/db.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php"); // Redirect to login page if not an admin
    exit();
}

// Handle file deletion
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // Fetch the file path to delete the file from the server
    $stmt = $mysqli->prepare("SELECT filepath FROM files WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $file = $result->fetch_assoc();
        $file_path = $file['filepath'];

        // Delete the file from the server
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        // Delete the file record from the database
        $stmt = $mysqli->prepare("DELETE FROM files WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
    }
    header("Location: admin.php");
    exit();
}

// Handle file update
if (isset($_POST['update_id'])) {
    $update_id = $_POST['update_id'];
    $new_filename = $_POST['filename'];

    $stmt = $mysqli->prepare("UPDATE files SET filename = ? WHERE id = ?");
    $stmt->bind_param("si", $new_filename, $update_id);
    $stmt->execute();
    header("Location: admin.php");
    exit();
}

// Handle file upload for editing
if (isset($_POST['edit_id'])) {
    $edit_id = $_POST['edit_id'];
    $file_upload = $_FILES['file'];
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($file_upload["name"]);

    // Move the uploaded file and update database
    if (move_uploaded_file($file_upload["tmp_name"], $target_file)) {
        $stmt = $mysqli->prepare("UPDATE files SET filename = ?, filepath = ? WHERE id = ?");
        $stmt->bind_param("ssi", $file_upload["name"], $target_file, $edit_id);
        $stmt->execute();
    }
    header("Location: admin.php");
    exit();
}

// Fetch users and files
$stmt = $mysqli->prepare("SELECT * FROM users");
$stmt->execute();
$users_result = $stmt->get_result();

$stmt_files = $mysqli->prepare("SELECT files.*, users.username FROM files JOIN users ON files.user_id = users.id");
$stmt_files->execute();
$files_result = $stmt_files->get_result();

include 'templates/header.php';
?>

<!-- Bootstrap CSS -->
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<!-- jQuery and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<h1>Admin Dashboard</h1>











<h2>Uploaded Files</h2>
<table class="table">
    <thead>
        <tr>
            <th>File ID</th>
            <th>Filename</th>
            <th>Uploaded By</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($file = $files_result->fetch_assoc()): ?>
            <tr>
                <td><?= $file['id'] ?></td>
                <td><?= $file['filename'] ?></td>
                <td><?= $file['username'] ?></td>
                <td>
                    <a href="<?= $file['filepath'] ?>" class="btn btn-info" target="_blank">View</a>
                    <button class="btn btn-warning" data-toggle="modal" data-target="#editModal<?= $file['id'] ?>">Edit</button>
                    <a href="?delete_id=<?= $file['id'] ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this file?');">Delete</a>
                    <a href="<?= $file['filepath'] ?>" class="btn btn-success" download>Download</a> <!-- Download button -->
                </td>
            </tr>

            <!-- Edit Modal -->
            <div class="modal fade" id="editModal<?= $file['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel<?= $file['id'] ?>" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editModalLabel<?= $file['id'] ?>">Edit File: <?= $file['filename'] ?></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="edit_id" value="<?= $file['id'] ?>">
                                <div class="form-group">
                                    <label for="filename">New Filename</label>
                                    <input type="text" class="form-control" name="filename" value="<?= $file['filename'] ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="file">Upload New File</label>
                                    <input type="file" class="form-control" name="file" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Update File</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </tbody>
</table>



<?php

// Handle user addition
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
    $role = $_POST['role'];

    $stmt = $mysqli->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $password, $role);
    $stmt->execute();
}

// Handle role update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_user'])) {
    $user_id = $_POST['user_id'];
    $new_role = $_POST['new_role'];

    $stmt = $mysqli->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->bind_param("si", $new_role, $user_id);
    $stmt->execute();
}

// Fetch users
$stmt = $mysqli->prepare("SELECT * FROM users");
$stmt->execute();
$users_result = $stmt->get_result();


?>





<h2>Manage Users</h2>
<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Role</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($user = $users_result->fetch_assoc()): ?>
            <tr>
                <td><?= $user['id'] ?></td>
                <td><?= $user['username'] ?></td>
                <td><?= $user['role'] ?></td>
                <td>
                    <form method="POST" action="">
                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                        <select name="new_role" class="form-control" required>
                            <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                        <button type="submit" name="update_user" class="btn btn-warning">Update Role</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>
  
<?php include 'templates/footer.php'; ?>

 

