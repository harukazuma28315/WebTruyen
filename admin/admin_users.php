<?php
session_start();
include '../db_connect.php'; // Đặt lại đường dẫn nếu cần
if (!isset($_SESSION['user_id']) || $_SESSION['level'] != 1) {
    header('Location: ../auth/login.php');
    exit();
}

// Thêm user (dành cho admin muốn tạo mới nhanh)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $level = (int)$_POST['level'];
    // Check email trùng
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute(); $stmt->store_result();
    if ($stmt->num_rows == 0) {
        $stmt->close();
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, level) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $name, $email, $password, $level);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: admin_users.php');
    exit();
}

// Sửa user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) {
    $user_id = (int)$_POST['user_id'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $level = (int)$_POST['level'];
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET name=?, email=?, password=?, level=? WHERE user_id=?");
        $stmt->bind_param("sssii", $name, $email, $password, $level, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET name=?, email=?, level=? WHERE user_id=?");
        $stmt->bind_param("ssii", $name, $email, $level, $user_id);
    }
    $stmt->execute();
    $stmt->close();
    header('Location: admin_users.php');
    exit();
}

// Xóa user
if (isset($_GET['delete_user'])) {
    $uid = (int)$_GET['delete_user'];
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id=?");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $stmt->close();
    header('Location: admin_users.php');
    exit();
}

// Lấy danh sách user
$users = $conn->query("SELECT * FROM users ORDER BY user_id DESC");

// Nếu sửa thì lấy dữ liệu user cần sửa
$editing = false;
$edit_user = [];
if (isset($_GET['edit_user'])) {
    $editing = true;
    $uid = (int)$_GET['edit_user'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id=?");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $res = $stmt->get_result();
    $edit_user = $res->fetch_assoc();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Quản lý người dùng - Admin</title>
    <style>
        body {
    background-color: #f8fbff;
    font-family: 'Segoe UI', sans-serif;
    color: #333;
    padding: 30px;
    line-height: 1.6;
}

h2 {
    text-align: center;
    color: #42a5f5;
    margin-bottom: 30px;
}

/* Form section */
.form-section {
    background-color: #e3f2fd;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 0 10px rgba(100, 181, 246, 0.2);
    max-width: 500px;
    margin: 0 auto 30px auto;
}

.form-section input,
.form-section select {
    width: 100%;
    padding: 10px;
    margin-top: 6px;
    margin-bottom: 16px;
    border: 1px solid #cfdff6;
    border-radius: 6px;
    transition: border-color 0.3s ease;
}

.form-section input:focus,
.form-section select:focus {
    border-color: #64b5f6;
    outline: none;
}

/* Buttons */
.btn {
    background-color: #64b5f6;
    color: white;
    border: none;
    padding: 8px 14px;
    text-decoration: none;
    border-radius: 6px;
    display: inline-block;
    transition: box-shadow 0.3s ease, transform 0.2s ease;
    cursor: pointer;
    margin-top: 5px;
}

.btn:hover {
    box-shadow: 0 0 10px #64b5f6, 0 0 20px #bbdefb;
    transform: translateY(-2px);
}

.btn-danger {
    background-color: #2196f3;
}

.btn-warning {
    background-color: #fcbf49;
    color: #fff;
}

.btn-danger:hover {
    box-shadow: 0 0 10px #2196f3, 0 0 20px #90caf9;
}

.btn-warning:hover {
    box-shadow: 0 0 10px #fcbf49, 0 0 20px #ffe0b3;
}

/* Table */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    background-color: #f8fbff;
    box-shadow: 0 0 8px rgba(0,0,0,0.05);
    border-radius: 10px;
    overflow: hidden;
}

th, td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #d3e3f3;
}

th {
    background-color: #bbdefb;
    color: #333;
    font-weight: bold;
}

tr:hover {
    background-color: #e3f2fd;
}

a.btn {
    margin-right: 6px;
}
.right-align {
    text-align: right;
    margin-top: 20px;
}

    </style>
</head>
<body>
    <h2>Quản lý người dùng</h2>
    <div class="form-section">
        <form method="post">
            <?php if ($editing): ?>
                <input type="hidden" name="user_id" value="<?= $edit_user['user_id'] ?>">
                <label>Sửa user:</label><br>
            <?php else: ?>
                <label>Thêm user mới:</label><br>
            <?php endif; ?>
            Tên: <input type="text" name="name" required value="<?= htmlspecialchars($editing ? $edit_user['name'] : '') ?>"><br>
            Email: <input type="email" name="email" required value="<?= htmlspecialchars($editing ? $edit_user['email'] : '') ?>"><br>
            Mật khẩu: <input type="password" name="password" <?= $editing ? '' : 'required' ?>> <?= $editing ? '(để trống nếu không đổi)' : '' ?><br>
            Quyền:
            <select name="level">
                <option value="0" <?= ($editing && $edit_user['level']==0) ? 'selected' : '' ?>>User</option>
                <option value="1" <?= ($editing && $edit_user['level']==1) ? 'selected' : '' ?>>Admin</option>
            </select><br>
            <?php if ($editing): ?>
                <button class="btn btn-warning" name="edit_user" type="submit">Cập nhật</button>
                <a class="btn" href="admin_users.php">Hủy</a>
            <?php else: ?>
                <button class="btn" name="add_user" type="submit">Thêm user</button>
            <?php endif; ?>
        </form>
    </div>
    <table>
        <tr>
            <th>ID</th>
            <th>Tên</th>
            <th>Email</th>
            <th>Quyền</th>
            <th>Ngày tạo</th>
            <th>Hành động</th>
        </tr>
        <?php while($user = $users->fetch_assoc()): ?>
        <tr>
            <td><?= $user['user_id'] ?></td>
            <td><?= htmlspecialchars($user['name']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><?= $user['level']==1 ? 'Admin' : 'User' ?></td>
            <td><?= $user['created_at'] ?></td>
            <td>
                <a class="btn" href="admin_users.php?edit_user=<?= $user['user_id'] ?>">Sửa</a>
                <a class="btn btn-danger" href="admin_users.php?delete_user=<?= $user['user_id'] ?>" onclick="return confirm('Xoá user này?')">Xoá</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <br>
    <div class="right-align">
    <a href="admin_novels.php" class="btn">Về quản lý truyện</a>
</div>

</body>
</html>
<?php $conn->close(); ?>
