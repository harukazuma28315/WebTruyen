<?php
session_start();
include '../db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index/index.php?login=1');
    exit();
}

echo "<h1>Chào bạn " . htmlspecialchars($_SESSION['name']) . "!</h1>";
echo "<p>Truyện đã tạo thành công!</p>";
echo "<a href='create.php'>Tạo thêm truyện mới</a>";

?>