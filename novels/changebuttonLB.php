<?php
session_start();
include '../db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Bạn chưa đăng nhập']);
    exit;
}

$user_id = $_SESSION['user_id'];
$novel_id = intval($_POST['novel_id'] ?? 0);

if (!$novel_id) {
    echo json_encode(['success' => false, 'message' => 'Thiếu ID truyện']);
    exit;
}

$sql_check = "SELECT 1 FROM user_library WHERE user_id = $user_id AND novel_id = $novel_id";
$result = mysqli_query($conn, $sql_check);

if (mysqli_num_rows($result) > 0) {
    // Đã có → xoá
    mysqli_query($conn, "DELETE FROM user_library WHERE user_id = $user_id AND novel_id = $novel_id");
    echo json_encode(['success' => true, 'in_library' => false]);
} else {
    // Chưa có → thêm
    mysqli_query($conn, "INSERT INTO user_library (user_id, novel_id) VALUES ($user_id, $novel_id)");
    echo json_encode(['success' => true, 'in_library' => true]);
}
