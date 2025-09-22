<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm = $_POST["confirm_password"];

    if (empty($name) || empty($email) || empty($password) || empty($confirm)) {
        echo "<script>alert('Vui lòng nhập đầy đủ thông tin!'); window.history.back();</script>";
        exit;
    }

    // Tiếp tục xử lý đăng ký...
}
?>
<?php
// Kết nối database
include '../db_connect.php';  // Kết nối cơ sở dữ liệu

// Kiểm tra nếu form gửi bằng POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Lấy dữ liệu từ form
    $name     = trim($_POST["name"]);
    $email    = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm  = $_POST["confirm_password"];

    // Kiểm tra xác nhận mật khẩu
    if ($password !== $confirm) {
        echo "<script>alert('Mật khẩu xác nhận không khớp!'); window.history.back();</script>";
        exit;
    }

    // Mã hóa mật khẩu
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Kiểm tra email đã tồn tại chưa
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "<script>alert('Email đã được đăng ký!'); window.history.back();</script>";
        exit;
    }
    $stmt->close();

    // Chèn dữ liệu vào bảng users
    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $hashedPassword);

    if ($stmt->execute()) {
        echo "<script>
    alert('Đăng ký thành công!');
    window.location.href = '../index/index.php?login=1';
</script>";

    } else {
        echo "<script>alert('Lỗi khi đăng ký: {$stmt->error}'); window.history.back();</script>";
    }

    $stmt->close();
}
$conn->close();
?>
