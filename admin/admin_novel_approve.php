<?php
session_start();
include '../db_connect.php';
if (!isset($_SESSION['user_id']) || $_SESSION['level'] != 1) {
    header('Location: ../auth/login.php');
    exit();
}
$id = $_GET['id'] ?? 0;
$action = $_GET['action'] ?? '';
if ($action == 'approve') $approval = 'approved';
else if ($action == 'reject') $approval = 'rejected';
else exit('Hành động không hợp lệ!');
$stmt = $conn->prepare("UPDATE novels SET approval=? WHERE novel_id=?");
$stmt->bind_param("si", $approval, $id);
$stmt->execute();
$stmt->close();
header('Location: admin_novels.php');
exit();
?>
