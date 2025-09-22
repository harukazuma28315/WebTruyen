<?php
session_start();
include '../db_connect.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['novel_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Thiếu dữ liệu']);
    exit;
}

$user_id = $_SESSION['user_id'];
$novel_id = intval($_POST['novel_id']);

$sql = "DELETE FROM reading_history WHERE user_id = $user_id AND novel_id = $novel_id";
if (mysqli_query($conn, $sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Không thể xoá khỏi lịch sử']);
}
?>