<?php
session_start();
include '../db_connect.php';
if (!isset($_SESSION['user_id']) || $_SESSION['level'] != 1) {
    header('Location: ../auth/login.php');
    exit();
}
$id = $_GET['id'] ?? 0;
$stmt = $conn->prepare("SELECT cover FROM novels WHERE novel_id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
if ($row && $row['cover'] && file_exists($row['cover'])) {
    unlink($row['cover']);
}
$stmt->close();
$stmt = $conn->prepare("DELETE FROM novels WHERE novel_id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();
header('Location: admin_novels.php');
exit();
?>
