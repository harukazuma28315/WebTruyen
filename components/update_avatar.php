<?php
session_start();
include '../db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index/index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    $file = $_FILES['avatar'];

    if ($file['error'] === 0 && $file['size'] < 2 * 1024 * 1024) { // Giới hạn 2MB
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new_name = 'user_' . $user_id . '_' . time() . '.' . $ext;
        $upload_dir = '../image/avatars/';
        $upload_path = $upload_dir . $new_name;

        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            // Cập nhật vào DB
            $stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE user_id = ?");
            $stmt->bind_param("si", $new_name, $user_id);
            $stmt->execute();

            // Cập nhật session
            $_SESSION['avatar'] = $new_name;

            header("Location: profile.php?success=1");
            exit();
        } else {
            echo "Lỗi khi lưu file ảnh.";
        }
    } else {
        echo "Ảnh không hợp lệ hoặc quá lớn.";
    }
} else {
    echo "Dữ liệu không hợp lệ.";
}
