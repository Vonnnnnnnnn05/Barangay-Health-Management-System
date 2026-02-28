<?php
session_start();
if (isset($_SESSION['user_id'])) {
    switch ($_SESSION['role']) {
        case 'admin':
            header("Location: Admin/dashboard.php");
            break;
        case 'health_worker':
            header("Location: Health-Workers/dashboard.php");
            break;
        case 'patient':
            header("Location: Patient/dashboard.php");
            break;
    }
    exit();
}
header("Location: login.php");
exit();
?>