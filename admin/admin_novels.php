<?php
session_start();
include '../db_connect.php'; // Đường dẫn tùy bạn
if (!isset($_SESSION['user_id']) || $_SESSION['level'] != 1) {
    header('Location: ../login.php');
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_full'])) {
    $novel_id = (int)$_POST['novel_id'];
    $stmt = $conn->prepare("UPDATE novels SET status = 'Full' WHERE novel_id = ?");
    $stmt->bind_param("i", $novel_id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_novels.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Quản lý truyện - Admin</title>
    
    
    
   <style>
    html {
	zoom: 67%;
}

   .btn-add {
    background: linear-gradient(135deg, #42a5f5, #1e88e5);
    color: white;
    font-weight: 600;
    padding: 10px 20px;
    border: none;
    font-size: 23px;
    border-radius: 10px;
    cursor: pointer;
    text-decoration: none;
    box-shadow: 0 4px 12px rgba(33, 150, 243, 0.3);
    transition: all 0.3s ease;
    display: inline-block;
}

.btn-add:hover {
    background: linear-gradient(135deg, #1e88e5, #1976d2);
    transform: translateY(-2px) scale(1.03);
    box-shadow: 0 8px 20px rgba(30, 136, 229, 0.5);
}

        /* Reset cơ bản */
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

body {
    background-color: #f5f9ff;
    color: #333;
    font-size: 18px;
}

/* Sidebar */
.sidebar {
    width: 220px;
    height: 100vh;
    background-color: #007acc;
    color: white;
    padding: 20px;
    float: left;
    position: fixed;
    top: 0;
    left: 0;
}

.sidebar-title {
    text-align: center;
    margin-bottom: 20px;
    font-size: 24px;
    font-weight: bold;
}

.sidebar-link {
    display: block;
    padding: 10px 15px;
    color: white;
    text-decoration: none;
    margin-bottom: 10px;
    border-radius: 6px;
    transition: background 0.3s;
}

.sidebar-link:hover {
    background-color: #005fa3;
}

/* Content Area */
.content {
    margin-left: 240px;
    padding: 30px;
}

.content-title {
    font-size: 28px;
    margin-bottom: 20px;
    color: #007acc;
}

/* Add button */
.btn-add-novel {
    padding: 10px 20px;
    background-color: #00aaff;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-weight: bold;
    transition: background 0.3s;
}

.btn-add-novel:hover {
    background-color: #008fd1;
}

/* Table */
.novel-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    background-color: white;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    border-radius: 6px;
    overflow: hidden;
}

.novel-table th, .novel-table td {
    padding: 12px 15px;
    border-bottom: 1px solid #eee;
    text-align: left;
}

.novel-table th {
    background-color: #e3f2fd;
    color: #007acc;
    font-weight: bold;
}

.novel-row:hover {
    background-color: #f0f8ff;
}

/* Buttons */
.btn {
    padding: 6px 12px;
    border: none;
    border-radius: 4px;
    color: white;
    cursor: pointer;
    font-size: 14px;
    text-decoration: none;
    margin: 2px;
    display: inline-block;
    transition: background 0.3s;
}

.btn-success { background-color: #28c76f; }
.btn-success:hover { background-color: #21a35a; }

.btn-warning { background-color: #ffbe0b; color: #222; }
.btn-warning:hover { background-color: #e6a700; }

.btn-danger { background-color: #ff4d4d; }
.btn-danger:hover { background-color: #e43f3f; }

.btn-edit { background-color: #0096c7; }
.btn-edit:hover { background-color: #007bb5; }

.btn-chapters { background-color: #00b4d8; }
.btn-chapters:hover { background-color: #009cb4; }

.action-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
}

/* Form inline */
.form-set-full {
    display: inline;
}

    </style>
</head>
<body>
    <div class="sidebar">
        <h2 class="sidebar-title">ADMIN</h2>
        <a class="sidebar-link" href="admin_novels.php">Quản lý truyện</a>
        <a class="sidebar-link" href="admin_users.php">Quản lý người dùng</a>
        <a class="sidebar-link" href="admin_categories.php">Quản lý thể loại</a>
        <a class="sidebar-link" href="admin_tags.php">Quản lý tags</a>
        <a class="sidebar-link" href="../auth/logout.php">Đăng xuất</a>
        <a class="sidebar-link" href="../index/index.php">Home</a>
    </div>

    <div class="content">
        <h2 class="content-title">Quản lý truyện</h2>
       <a class="btn-add" href="admin_novel_form.php?action=add">+ Thêm truyện</a>

        <br><br>
        <table class="novel-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tiêu đề</th>
                    <th>Thể loại</th>
                    <th>Tags</th>
                    <th>Người đăng</th>
                    <th>Trạng thái duyệt</th>
                    <th>Số chương</th>
                    <th>Trạng thái truyện</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT n.*, u.name as poster, c.name as category, 
                            GROUP_CONCAT(t.name ORDER BY t.tag_id ASC LIMIT 2) AS tags
                        FROM novels n
                        LEFT JOIN users u ON n.created_by = u.user_id
                        LEFT JOIN categories c ON n.category_id = c.category_id
                        LEFT JOIN novel_tag nt ON n.novel_id = nt.novel_id
                        LEFT JOIN tags t ON nt.tag_id = t.tag_id
                        GROUP BY n.novel_id
                        ORDER BY n.novel_id DESC";

                $result = $conn->query($sql);
                while ($row = $result->fetch_assoc()):
                    // Đếm số chương
                    $stmt = $conn->prepare("SELECT COUNT(*) FROM chapters WHERE novel_id=?");
                    $stmt->bind_param("i", $row['novel_id']);
                    $stmt->execute();
                    $stmt->bind_result($chapter_count);
                    $stmt->fetch();
                    $stmt->close();
                ?>
                <tr class="novel-row">
                    <td><?= $row['novel_id'] ?></td>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><?= htmlspecialchars($row['category'] ?? '---') ?></td>
                    <td><?= htmlspecialchars($row['tags'] ?? '---') ?></td>
                    <td><?= htmlspecialchars($row['poster'] ?? '---') ?></td>
                    <td><?= $row['approval'] ?></td>
                    <td><?= $chapter_count ?></td>
                    <td>
                        <?= htmlspecialchars($row['status']) ?>
                        <?php if ($row['status'] !== 'Full'): ?>
                            <form class="form-set-full" method="post" style="display:inline;">
                                <input type="hidden" name="novel_id" value="<?= $row['novel_id'] ?>">
                                <button class="btn btn-warning btn-set-full" name="set_full" onclick="return confirm('Chuyển trạng thái truyện sang Full?')">Set Full</button>
                            </form>
                        <?php endif; ?>
                    </td>
                    <td class="action-buttons">
                        <a class="btn btn-edit" href="admin_novel_form.php?action=edit&id=<?= $row['novel_id'] ?>">Sửa</a>
                        <a class="btn btn-danger btn-delete" href="admin_novel_delete.php?id=<?= $row['novel_id'] ?>" onclick="return confirm('Xác nhận xoá?')">Xoá</a>
                        <?php if ($row['approval']=='pending'): ?>
                            <a class="btn btn-success btn-approve" href="admin_novel_approve.php?id=<?= $row['novel_id'] ?>&action=approve">Duyệt</a>
                            <a class="btn btn-warning btn-reject" href="admin_novel_approve.php?id=<?= $row['novel_id'] ?>&action=reject">Từ chối</a>
                        <?php endif; ?>
                        <a class="btn btn-chapters" href="admin_chapters.php?novel_id=<?= $row['novel_id'] ?>">Chương</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>

</html>
<?php $conn->close(); ?>
