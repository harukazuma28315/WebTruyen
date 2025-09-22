<?php
session_start();
include '../db_connect.php';
if (!isset($_SESSION['user_id'])) {
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
    header("Location: add-chap.php?novel_id=$novel_id");
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
    header("Location: add-chap.php?novel_id=$novel_id");
    exit();
}

// Xử lý xóa chương
if (isset($_GET['delete_chapter'])) {
    $chapter_id = (int)$_GET['delete_chapter'];
    $stmt = $conn->prepare("DELETE FROM chapters WHERE chapter_id=?");
    $stmt->bind_param("i", $chapter_id);
    $stmt->execute();
    $stmt->close();
    header("Location: add-chap.php?novel_id=$novel_id");
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

<?php
//form của việc thêm ảnh vào chương
$message = "";
$novel_id = isset($_GET['novel_id']) ? intval($_GET['novel_id']) : 0;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['chapter_id'], $_POST['base_path'], $_FILES['images'])) {
    $chapter_id = intval($_POST['chapter_id']);
    $novel_id = intval($_POST['novel_id']);
    $base_path = trim($_POST['base_path']);
    $base_path = rtrim($base_path, "/"); // xóa dấu / cuối nếu có

    // Lấy thông tin truyện và chương
    $stmtNovel = $conn->prepare("SELECT title FROM novels WHERE novel_id = ?");
    $stmtNovel->bind_param("i", $novel_id);
    $stmtNovel->execute();
    $truyen = $stmtNovel->get_result()->fetch_assoc();

    $stmtChap = $conn->prepare("SELECT number FROM chapters WHERE chapter_id = ?");
    $stmtChap->bind_param("i", $chapter_id);
    $stmtChap->execute();
    $chapter = $stmtChap->get_result()->fetch_assoc();

    if ($truyen && $chapter) {
        $chapter_folder = "chap_" . $chapter['number'];
        // Đây là đường vật lý thực sự trên server
        $upload_dir = __DIR__ . "/../" . $base_path . "/" . $chapter_folder . "/";

        // Đây là đường tương đối để lưu vào DB
        $web_path = $base_path . "/" . $chapter_folder . "/";


        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $total = count($_FILES['images']['name']);
        for ($i = 0; $i < $total; $i++) {
            $tmp = $_FILES['images']['tmp_name'][$i];
            if (!is_uploaded_file($tmp)) continue;

            $filename = str_pad($i + 1, 3, '0', STR_PAD_LEFT) . ".jpg";
            $relative_path = $upload_dir . $filename;

            $full_path = $upload_dir . $filename;     // đường lưu vật lý
            $image_url = $web_path . $filename;       // đường lưu DB

            if (move_uploaded_file($tmp, $full_path)) {
                $stmt = $conn->prepare("INSERT INTO chapter_images (chapter_id, novel_id, image_url, image_order) VALUES (?, ?, ?, ?)");
                $order = $i + 1;
                $stmt->bind_param("iisi", $chapter_id, $novel_id, $image_url, $order);
                $stmt->execute();
            }
        }

        header("Location: " . $_SERVER['PHP_SELF'] . "?novel_id={$novel_id}&success=1");
        exit();
    } else {
        header("Location: " . $_SERVER['PHP_SELF'] . "?novel_id={$novel_id}&error=novel_or_chapter_invalid");
        exit();
    }
}

if (isset($_GET['success'])) {
    $message = "<p class='success'>✅ Thêm ảnh chương thành công!</p>";
} elseif (isset($_GET['error'])) {
    $message = "<p class='error'>❌ Lỗi: " . htmlspecialchars($_GET['error']) . "</p>";
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Quản lý chương | <?= htmlspecialchars($novel_title ?? 'WebLore') ?></title>
    <link rel="stylesheet" href="../css/add-chap.css">
</head>
<body>

<h2>Quản lý chương cho truyện: <?= htmlspecialchars($novel_title ?? '') ?></h2>

<div class="tabs">
    <button class="tab-btn active" onclick="showTab('tab1', this)">📖 Quản lý chương chữ</button>
    <button class="tab-btn" onclick="showTab('tab2', this)">🖼️ Thêm ảnh chương truyện tranh</button>
</div>

<!-- Tab 1: Truyện chữ -->
<div id="tab1" class="tab-content active">
    <form method="post">
        <?php if ($editing): ?>
            <input type="hidden" name="chapter_id" value="<?= $edit_chapter['chapter_id'] ?>">
            <label>Sửa chương:</label>
        <?php else: ?>
            <label>Thêm chương mới:</label>
        <?php endif; ?>
        <input type="text" name="chapter_title" required placeholder="Tiêu đề chương"
               value="<?= htmlspecialchars($editing ? $edit_chapter['title'] : '') ?>">
        <textarea name="chapter_content" rows="6" placeholder="Nội dung chương"><?= htmlspecialchars($editing ? $edit_chapter['content'] : '') ?></textarea>
        <?php if ($editing): ?>
            <button class="btn btn-warning" name="edit_chapter" type="submit">Cập nhật</button>
            <a class="btn" href="add-chap.php?novel_id=<?= $novel_id ?>">Hủy</a>
        <?php else: ?>
            <button class="btn" name="add_chapter" type="submit">Thêm chương</button>
        <?php endif; ?>
    </form>

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
                <a class="btn" href="?novel_id=<?= $novel_id ?>&edit_chapter=<?= $chap['chapter_id'] ?>">Sửa</a>
                <a class="btn btn-danger" href="?novel_id=<?= $novel_id ?>&delete_chapter=<?= $chap['chapter_id'] ?>" onclick="return confirm('Xoá chương này?')">Xoá</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<!-- Tab 2: Upload ảnh -->
<!-- Form Tab 2 -->
<div id="tab2" class="tab-content">
    <?= $message ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="novel_id" value="<?= $novel_id ?>">

        <?php if ($novel_id): ?>
            <label for="chapter_id">Chọn Chương:</label>
            <select name="chapter_id" required>
                <option value="">-- Chọn chương --</option>
                <?php
                $chapters = $conn->query("SELECT chapter_id, number, title FROM chapters WHERE novel_id = $novel_id ORDER BY number ASC");
                while ($c = $chapters->fetch_assoc()):
                ?>
                    <option value="<?= $c['chapter_id'] ?>">Chương <?= $c['number'] ?>: <?= htmlspecialchars($c['title']) ?></option>
                <?php endwhile; ?>
            </select>

            <label for="base_path">Thư mục lưu ảnh (VD: uploads/comics/One_Piece)</label><br>
            <label for="base_path">Dấu cách thì dùng "_"</label><br>
            <label for="base_path">Thêm tên truyện vào</label>
            <input type="text" name="base_path" id="base_path" required value="uploads/comics/">

            <label for="images[]">Chọn ảnh chương:</label>
            <input type="file" name="images[]" multiple accept="image/*" required>

            <button type="submit">📤 Thêm ảnh vào chương</button>
        <?php endif; ?>
    </form>
</div>
    <br>
    <a href="profile.php" class="btn">Quay lại Hồ sơ</a>

<script>
function showTab(tabId, btn) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
    document.getElementById(tabId).classList.add('active');
    btn.classList.add('active');
}

function onNovelChange() {
    const selectedId = document.getElementById("novel_id").value;
    if (selectedId) {
        window.location.href = "?novel_id=" + selectedId;
    }
}
</script>

</body>
</html>
