<?php
include 'session.php';
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Fetch user data from database
    $stmt = $mysqli->prepare("SELECT id, role FROM users WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $role);
        $stmt->fetch();
        
        // Set session variables
        $_SESSION['user_id'] = $user_id;
        $_SESSION['role'] = $role;

        // Redirect based on role
        if ($role === 'admin') {
            header("Location: admin.php");
        } else {
            header("Location: dashboard.php"); // Redirect to user dashboard
        }
        exit();
    } else {
        echo "<script>alert('Invalid username or password');</script>";
    }
}

include 'header.php';
?>

<form method="POST" action="">
    <div class="form-group">
        <label for="username">Username</label>
        <input type="text" class="form-control" name="username" required>
    </div>
    <div class="form-group">
        <label for="password">Password</label>
        <input type="password" class="form-control" name="password" required>
    </div>
    <button type="submit" class="btn btn-primary">Login</button>
</form>

<?php include 'footer.php'; ?>
