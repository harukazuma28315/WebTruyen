<?php
session_start();
include '../db_connect.php';
if (!isset($_SESSION['user_id']) || $_SESSION['level'] != 1) {
    header('Location: ../auth/login.php');
    exit();
}

// Thêm category mới
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = trim($_POST['name']);
    $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $stmt->close();
    header('Location: admin_categories.php');
    exit();
}

// Sửa category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_category'])) {
    $category_id = (int)$_POST['category_id'];
    $name = trim($_POST['name']);
    $stmt = $conn->prepare("UPDATE categories SET name=? WHERE category_id=?");
    $stmt->bind_param("si", $name, $category_id);
    $stmt->execute();
    $stmt->close();
    header('Location: admin_categories.php');
    exit();
}

// Xóa category
if (isset($_GET['delete_category'])) {
    $category_id = (int)$_GET['delete_category'];
    $stmt = $conn->prepare("DELETE FROM categories WHERE category_id=?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $stmt->close();
    header('Location: admin_categories.php');
    exit();
}

// Lấy danh sách categories
$categories = $conn->query("SELECT * FROM categories ORDER BY category_id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Quản lý thể loại - Admin</title>
    <style>
        body {
    background-color: #f8fbff;
    font-family: 'Segoe UI', sans-serif;
    color: #333;
    padding: 30px;
    line-height: 1.6;
}

/* Tiêu đề */
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

.form-section input {
    width: 100%;
    padding: 10px;
    margin-top: 6px;
    margin-bottom: 16px;
    border: 1px solid #cfdff6;
    border-radius: 6px;
    transition: border-color 0.3s ease;
}

.form-section input:focus {
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
    font-size: 14px;
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
    margin-top: 30px;
    background-color: #ffffff;
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
    font-weight: bold;
    color: #333;
}

tr:hover {
    background-color: #e3f2fd;
}

/* Cột tên thể loại – rút gọn nếu dài quá */
td:nth-child(2) {
    max-width: 200px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Mở rộng ô hành động */
td:nth-child(3) {
    min-width: 180px;
    white-space: nowrap;
    text-align: left;
}

/* Canh phải nút về quản lý truyện */
a[href*="admin_novels.php"] {
    margin-left:
    text-align: right;
    margin-top: 20px;
}
.right-align {
    text-align: right;
    margin-top: 20px;
}
    </style>
</head>
<body>
    <h2>Quản lý thể loại (Category)</h2>
    <div class="form-section">
        <form method="post">
            <?php if (isset($_GET['edit_category'])): ?>
                <input type="hidden" name="category_id" value="<?= $_GET['edit_category'] ?>">
                <label>Sửa thể loại:</label><br>
            <?php else: ?>
                <label>Thêm thể loại mới:</label><br>
            <?php endif; ?>
            Tên thể loại: <input type="text" name="name" required value="<?= htmlspecialchars(isset($category_name) ? $category_name : '') ?>"><br>
            <?php if (isset($_GET['edit_category'])): ?>
                <button class="btn btn-warning" name="edit_category" type="submit">Cập nhật</button>
                <a class="btn" href="admin_categories.php">Hủy</a>
            <?php else: ?>
                <button class="btn" name="add_category" type="submit">Thêm thể loại</button>
            <?php endif; ?>
        </form>
    </div>

    <table>
        <tr>
            <th>ID</th>
            <th>Tên thể loại</th>
            <th>Hành động</th>
        </tr>
        <?php while ($category = $categories->fetch_assoc()): ?>
        <tr>
            <td><?= $category['category_id'] ?></td>
            <td><?= htmlspecialchars($category['name']) ?></td>
            <td>
                <a class="btn" href="admin_categories.php?edit_category=<?= $category['category_id'] ?>">Sửa</a>
                <a class="btn btn-danger" href="admin_categories.php?delete_category=<?= $category['category_id'] ?>" onclick="return confirm('Xoá thể loại này?')">Xoá</a>
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
