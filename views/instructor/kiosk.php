<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor' ) {
    $_SESSION['error'] = "Unauthorized access. Please login as Instructor.";
    header("Location: ../auth/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head><title>Instructor Dashboard - FitCampus</title></head>
<body style="background:#131313; color:#fff; font-family:sans-serif; padding:40px;">
    <h2>Welcome Instructor: <?php echo htmlspecialchars($_SESSION['full_name']); ?></h2>
    <p>Role: Instructor | Status: Active</p>
    <a href="../../backend/auth/logout.php" style="color:#dfb7ff;">Logout</a>
</body>
</html>