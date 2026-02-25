<?php
session_start();
if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: dashboard.php');
} else {
    header('Location: login.php');
}
exit;
