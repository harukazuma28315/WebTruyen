<?php
session_start();
include '../db_connect.php';
if (!isset($_SESSION['user_id']) || $_SESSION['level'] != 1) {
    header('Location: ../auth/login.php');
    exit();
}

$novel_id = isset($_GET['novel_id']) ? (int)$_GET['novel_id'] : 0;

// Lấy tên truyện
$stmt = $conn->prepare("SELECT title FROM novels WHERE novel_id=?");
$stmt->bind_param("i", $novel_id);
$stmt->execute();
$stmt->bind_result($novel_title);
$stmt->fetch();
$stmt->close();

// Xử lý thêm chương
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_chapter'])) {
    $chapter_title = trim($_POST['chapter_title']);
    $chapter_content = trim($_POST['chapter_content']);
    // Tự động lấy số thứ tự chương mới (max+1)
    $stmt = $conn->prepare("SELECT COALESCE(MAX(number),0)+1 FROM chapters WHERE novel_id=?");
    $stmt->bind_param("i", $novel_id);
    $stmt->execute();
    $stmt->bind_result($chapter_number);
    $stmt->fetch();
    $stmt->close();

    // Thêm chương mới
    $stmt = $conn->prepare("INSERT INTO chapters (novel_id, title, content, number) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issi", $novel_id, $chapter_title, $chapter_content, $chapter_number);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_chapters.php?novel_id=$novel_id");
    exit();
}

// Xử lý sửa chương
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_chapter'])) {
    $chapter_id = (int)$_POST['chapter_id'];
    $chapter_title = trim($_POST['chapter_title']);
    $chapter_content = trim($_POST['chapter_content']);
    $stmt = $conn->prepare("UPDATE chapters SET title=?, content=? WHERE chapter_id=?");
    $stmt->bind_param("ssi", $chapter_title, $chapter_content, $chapter_id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_chapters.php?novel_id=$novel_id");
    exit();
}

// Xử lý xóa chương
if (isset($_GET['delete_chapter'])) {
    $chapter_id = (int)$_GET['delete_chapter'];
    $stmt = $conn->prepare("DELETE FROM chapters WHERE chapter_id=?");
    $stmt->bind_param("i", $chapter_id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_chapters.php?novel_id=$novel_id");
    exit();
}

// Nếu đang sửa chương, lấy dữ liệu chương để hiển thị lên form
$editing = false;
$edit_chapter = [];
if (isset($_GET['edit_chapter'])) {
    $editing = true;
    $chapter_id = (int)$_GET['edit_chapter'];
    $stmt = $conn->prepare("SELECT * FROM chapters WHERE chapter_id=?");
    $stmt->bind_param("i", $chapter_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $edit_chapter = $res->fetch_assoc();
    $stmt->close();
}

// Lấy tất cả chương của truyện
$stmt = $conn->prepare("SELECT * FROM chapters WHERE novel_id=? ORDER BY number ASC");
$stmt->bind_param("i", $novel_id);
$stmt->execute();
$chapters = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Quản lý chương | <?= htmlspecialchars($novel_title) ?></title>
    <style>
        body { font-family: Arial; margin:0; }
        .btn { padding: 5px 12px; margin:0 2px; text-decoration: none;background:#2980b9; color:white; border:none; border-radius:3px; cursor:pointer;}
        .btn-danger { background:#e74c3c; }
        .btn-warning { background:#f39c12; }
        .btn:hover { opacity:0.9; }
        table { border-collapse:collapse; width:100%; margin-top:20px;}
        th,td { border:1px solid #888; padding:7px; }
        th { background: #eee; }
        .form-section { background: #f5f6fa; padding:18px 18px 8px 18px; border-radius:8px; margin-bottom: 22px; width:98%; }
        label { font-weight: bold;}
    </style>
</head>
<body>
    <h2>Quản lý chương của truyện: <?= htmlspecialchars($novel_title) ?></h2>

    <!-- Form thêm/sửa chương -->
    <div class="form-section">
        <form method="post">
            <?php if ($editing): ?>
                <input type="hidden" name="chapter_id" value="<?= $edit_chapter['chapter_id'] ?>">
                <label>Sửa chương:</label><br>
            <?php else: ?>
                <label>Thêm chương mới:</label><br>
            <?php endif; ?>
            Tiêu đề chương:<br>
            <input type="text" name="chapter_title" required value="<?= htmlspecialchars($editing ? $edit_chapter['title'] : '') ?>" style="width:50%;"><br>
            Nội dung chương:<br>
            <textarea name="chapter_content" rows="6" required style="width:70%;"><?= htmlspecialchars($editing ? $edit_chapter['content'] : '') ?></textarea><br>
            <?php if ($editing): ?>
                <button class="btn btn-warning" name="edit_chapter" type="submit">Cập nhật</button>
                <a class="btn" href="admin_chapters.php?novel_id=<?= $novel_id ?>">Hủy</a>
            <?php else: ?>
                <button class="btn" name="add_chapter" type="submit">Thêm chương</button>
            <?php endif; ?>
        </form>
    </div>

    <table>
        <tr>
            <th>STT</th>
            <th>Tiêu đề chương</th>
            <th>Ngày tạo</th>
            <th>Hành động</th>
        </tr>
        <?php while ($chap = $chapters->fetch_assoc()): ?>
        <tr>
            <td><?= $chap['number'] ?></td>
            <td><?= htmlspecialchars($chap['title']) ?></td>
            <td><?= $chap['created_at'] ?></td>
            <td>
                <a class="btn" href="admin_chapters.php?novel_id=<?= $novel_id ?>&edit_chapter=<?= $chap['chapter_id'] ?>">Sửa</a>
                <a class="btn btn-danger" href="admin_chapters.php?novel_id=<?= $novel_id ?>&delete_chapter=<?= $chap['chapter_id'] ?>" onclick="return confirm('Xoá chương này?')">Xoá</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <br>
    <a href="admin_novels.php" class="btn">Quay lại danh sách truyện</a>
</body>
</html>
<?php $conn->close(); ?>
