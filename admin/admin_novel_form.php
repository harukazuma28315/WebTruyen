<?php
session_start();
include '../db_connect.php';
if (!isset($_SESSION['user_id']) || $_SESSION['level'] != 1) {
    header('Location: ../auth/login.php');
    exit();
}

$action = $_GET['action'] ?? 'add';
$id = $_GET['id'] ?? 0;
$title = $desc = $cover = $category_id = '';
$editMode = false;

if ($action == 'edit' && $id) {
    $stmt = $conn->prepare("SELECT * FROM novels WHERE novel_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $novel = $result->fetch_assoc();
    $stmt->close();
    if ($novel) {
        $editMode = true;
        $title = $novel['title'];
        $desc = $novel['description'];
        $cover = $novel['cover'];
        $category_id = $novel['category_id'];
    }
}

// Lấy danh sách tags từ bảng tags
$sql_tags = "SELECT * FROM tags";
$result_tags = $conn->query($sql_tags);
$tags = [];
while ($tag = $result_tags->fetch_assoc()) {
    $tags[] = $tag;
}

// Lấy các tag đã chọn nếu là chế độ chỉnh sửa
$selected_tags = [];
if ($editMode) {
    $stmt = $conn->prepare("SELECT t.tag_id FROM tags t 
                            JOIN novel_tag nt ON t.tag_id = nt.tag_id 
                            WHERE nt.novel_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $selected_tags[] = $row['tag_id'];
    }
    $stmt->close();
}

// Xử lý submit form khi người dùng nhấn "Lưu" hoặc "Thêm"
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $desc = trim($_POST['description']);
    $category_id = (int)$_POST['category_id'];
    $cover_upload = $cover;
    
    // Nếu có ảnh bìa mới, tiến hành upload
    if (!empty($_FILES['cover']['name'])) {
        $cover_upload = '../images/' . time() . '_' . basename($_FILES['cover']['name']);
        move_uploaded_file($_FILES['cover']['tmp_name'], $cover_upload);
    }

    if ($editMode) {
        // Cập nhật thông tin truyện
        $stmt = $conn->prepare("UPDATE novels SET title=?, description=?, cover=?, category_id=? WHERE novel_id=?");
        $stmt->bind_param("sssii", $title, $desc, $cover_upload, $category_id, $id);
        $stmt->execute();
        $stmt->close();

        // Xóa các tag cũ của truyện trước khi thêm lại các tag mới
        $delete_sql = "DELETE FROM novel_tag WHERE novel_id = ?";
        $stmt = $conn->prepare($delete_sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        // Nếu có tag đã chọn, lưu vào bảng novel_tag
        if (isset($_POST['tags'])) {
            $tags_selected = $_POST['tags']; // Mảng các tag đã chọn
            $insert_sql = "INSERT INTO novel_tag (novel_id, tag_id) VALUES (?, ?)";
            $stmt = $conn->prepare($insert_sql);
            foreach ($tags_selected as $tag_id) {
                $stmt->bind_param("ii", $id, $tag_id);
                $stmt->execute();
            }
            $stmt->close();
        }

        echo "<script>alert('Đã cập nhật truyện!');window.location='admin_novels.php';</script>";
        exit();
    } else {
        // Thêm truyện mới
        $admin_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("INSERT INTO novels (title, description, cover, category_id, created_by, approval) VALUES (?, ?, ?, ?, ?, 'approved')");
        $stmt->bind_param("sssii", $title, $desc, $cover_upload, $category_id, $admin_id);
        $stmt->execute();
        $novel_id = $stmt->insert_id; // Lấy ID của truyện mới được thêm
        $stmt->close();

        // Nếu có tag đã chọn, lưu vào bảng novel_tag
        if (isset($_POST['tags'])) {
            $tags_selected = $_POST['tags']; // Mảng các tag đã chọn
            $insert_sql = "INSERT INTO novel_tag (novel_id, tag_id) VALUES (?, ?)";
            $stmt = $conn->prepare($insert_sql);
            foreach ($tags_selected as $tag_id) {
                $stmt->bind_param("ii", $novel_id, $tag_id);
                $stmt->execute();
            }
            $stmt->close();
        }

        echo "<script>alert('Đã thêm truyện!');window.location='admin_novels.php';</script>";
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= $editMode ? 'Sửa' : 'Thêm' ?> truyện</title>
    <style>body {
  background-color: #f5f7fa;
  font-family: 'Segoe UI', 'Roboto', sans-serif;
  color: #333;
  margin: 0;
  padding: 40px 20px;
}

.container {
  max-width: 960px;
  margin: 0 auto;
}

h2 {
  text-align: center;
  font-size: 28px;
  color: #1976d2;
  margin-bottom: 40px;
  font-weight: 600;
}

/* FORM MODERN CARD */
.form-section {
  background: #ffffff;
  border-radius: 16px;
  padding: 30px;
  box-shadow: 0 10px 30px rgba(25, 118, 210, 0.08);
  margin-bottom: 40px;
}

.form-section textarea {
  width: 100%;
  padding: 14px 16px;
  border: 1px solid #cfd8dc;
  border-radius: 10px;
  font-size: 15px;
  background-color: #f9fcff;
  transition: 0.3s;
  min-height: 180px; /* ✅ tăng chiều cao */
  resize: vertical;  /* ✅ cho phép kéo */
  line-height: 1.6;
}

.form-section textarea {
  width: 95%;
  padding: 14px 16px;
  border: 1px solid #cfd8dc;
  border-radius: 10px;
  font-size: 15px;
  margin-bottom: 20px;
  background-color: #f9fcff;
  transition: 0.3s;
}
.form-section input[type="text"] {
  width: 95%;
  padding: 14px 16px;
  border: 1px solid #cfd8dc;
  border-radius: 10px;
  font-size: 15px;
  margin-bottom: 20px;
  background-color: #f9fcff;
  transition: 0.3s;
}



.form-section input:focus,
.form-section textarea:focus {
  border-color: #42a5f5;
  box-shadow: 0 0 12px rgba(66, 165, 245, 0.3);
  outline: none;
}

/* BUTTON MODERN */
.btn {
  display: inline-block;
  padding: 10px 20px;
  background: #42a5f5;
  color: #fff;
  font-weight: 600;
  font-size: 14px;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  text-decoration: none;
  transition: all 0.3s ease;
  box-shadow: 0 4px 14px rgba(66, 165, 245, 0.3);
  margin-right: 10px;
}

.btn:hover {
  background: #1e88e5;
  box-shadow: 0 0 20px rgba(66, 165, 245, 0.5);
  transform: translateY(-2px);
}

.btn-warning {
  background: #ffb300;
  color: white;
}

.btn-warning:hover {
  background: #ffa000;
  box-shadow: 0 0 18px rgba(255, 179, 0, 0.4);
}

.btn-danger {
  background: #ef5350;
}

.btn-danger:hover {
  background: #e53935;
  box-shadow: 0 0 18px rgba(239, 83, 80, 0.4);
}

/* TABLE MODERN */
table {
  width: 100%;
  border-collapse: collapse;
  background-color: #fff;
  box-shadow: 0 8px 18px rgba(0, 0, 0, 0.04);
  border-radius: 12px;
  overflow: hidden;
}

th, td {
  padding: 16px 20px;
  text-align: left;
  border-bottom: 1px solid #e0e0e0;
  font-size: 15px;
}

th {
  background: #e3f2fd;
  font-weight: 600;
  color: #0d47a1;
}

tr:hover {
  background-color: #f1faff;
}

td:nth-child(2) {
  max-width: 220px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

td:nth-child(3) {
  min-width: 180px;
  white-space: nowrap;
}

/* FOOTER NAV */
.right-align {
  text-align: right;
  margin-top: 30px;
}
.form-section select,
.form-section input[type="file"] {
  width: 95%;
  padding: 12px 14px;
  border: 1px solid #cfd8dc;
  border-radius: 10px;
  font-size: 15px;
  margin-bottom: 20px;
  background-color: #f9fcff;
}

.form-section select:focus,
.form-section input[type="file"]:focus {
  border-color: #42a5f5;
  box-shadow: 0 0 12px rgba(66, 165, 245, 0.3);
  outline: none;
}

/* Tag checkbox grid */
.tag-checkbox {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 10px;
  margin-bottom: 20px;
}

.tag-checkbox label {
  font-weight: normal;
  display: flex;
  align-items: center;
  font-size: 14px;
  gap: 6px;
}
.form-section label {
  font-weight: 600;
  color: #0d47a1;
  margin: 20px 0 8px;
  font-size: 16px;
  letter-spacing: 0.3px;
  display: block;
}

</style>
</head>
<body>
<div class="container">
  <h2><?= $editMode ? 'Sửa' : 'Thêm' ?> truyện</h2>

  <form method="post" enctype="multipart/form-data" class="form-section">
    <label for="title">Tiêu đề:</label>
    <input type="text" name="title" id="title" value="<?= htmlspecialchars($title) ?>" required>

    <label for="description">Mô tả tóm tắt:</label>
    <textarea name="description" id="description" required><?= htmlspecialchars($desc) ?></textarea>

    <?php if ($cover): ?>
      <label>Ảnh bìa hiện tại:</label><br>
      <img src="<?= $cover ?>" style="height:60px; border-radius: 6px; margin-bottom: 16px;">
    <?php endif; ?>

    <label for="cover">Ảnh bìa mới:</label>
    <input type="file" name="cover" id="cover">

    <label for="category_id">Thể loại:</label>
    <select name="category_id" id="category_id" required>
      <?php
      $cats = $conn->query("SELECT * FROM categories");
      while ($cat = $cats->fetch_assoc()) {
          $sel = $cat['category_id'] == $category_id ? "selected" : "";
          echo '<option value="'.$cat['category_id'].'" '.$sel.'>'.$cat['name'].'</option>';
      }
      ?>
    </select>

    <label>Chọn tag:</label>
    <div class="tag-checkbox">
      <?php foreach ($tags as $tag): ?>
        <label>
          <input type="checkbox" name="tags[]" value="<?= $tag['tag_id'] ?>" 
            <?= in_array($tag['tag_id'], $selected_tags) ? 'checked' : '' ?>>
          <?= htmlspecialchars($tag['name']) ?>
        </label>
      <?php endforeach; ?>
    </div>

    <button type="submit" class="btn"><?= $editMode ? 'Lưu' : 'Thêm' ?></button>
    <a href="admin_novels.php" class="btn btn-warning">Quay lại</a>
  </form>
</div>
</body>

</html>
<?php $conn->close(); ?>
