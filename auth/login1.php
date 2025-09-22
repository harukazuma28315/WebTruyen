<?php
session_start();

include '../db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $login_name     = trim($_POST["login_name"]);
    $login_password = $_POST["login_password"];

    // Lấy thêm trường level để phân quyền
    $stmt = $conn->prepare("SELECT user_id, name, email, password, avatar, level FROM users WHERE name = ? OR email = ?");
    $stmt->bind_param("ss", $login_name, $login_name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($login_password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['avatar']  = $user['avatar'] ?? 'default-avatar.png';
            $_SESSION['level']   = $user['level']; // <-- DÒNG PHÂN QUYỀN

            if (isset($_SESSION['redirect_after_login'])) {
                $redirect_url = $_SESSION['redirect_after_login'];
                unset($_SESSION['redirect_after_login']);
                header("Location: $redirect_url");
                exit;
            } else {
                header("Location: ../novels/create.php");
                exit;
            }
        } else {
            echo "<script>alert('Sai mật khẩu!'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Không tìm thấy người dùng!'); window.history.back();</script>";
    }

    $stmt->close();
}

$conn->close();
?>
