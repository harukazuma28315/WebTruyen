<?php
session_start();
include '../db_connect.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ../auth/login1_from.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['name'];

$stmt = $conn->prepare("SELECT 
    n.novel_id, 
    n.title, 
    n.description, 
    n.cover, 
    n.approval, 
    a.name AS author_name,
    c.name AS theloai_chinh,
    GROUP_CONCAT(t.name SEPARATOR ', ') AS theloai_phu
FROM novels n
LEFT JOIN novel_author na ON n.novel_id = na.novel_id
LEFT JOIN authors a ON na.author_id = a.author_id
LEFT JOIN categories c ON n.category_id = c.category_id
LEFT JOIN novel_tag nt ON n.novel_id = nt.novel_id
LEFT JOIN tags t ON nt.tag_id = t.tag_id
WHERE n.created_by = ?
GROUP BY n.novel_id
ORDER BY n.created_at DESC");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../css/create_user.css?v=<?php echo time(); ?>">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body, html { height: 100%; font-family: 'Segoe UI', sans-serif; }

    .dashboard-wrapper {
      display: flex;
      height: 100vh;
      background-color: #f4f4f4;
    }

    .sidebar1 {
      background-color: #1e272e;
      color: #fff;
      width: 240px;
      display: flex;
      flex-direction: column;
      padding: 20px;
    }

    .sidebar1 .logo-right {
      width: 100%;
      max-width: 180px;
      margin: 0 auto 20px;
    }
    .sidebar1 nav a {
	display: flex;
	align-items: center;
	gap: 14px;
	padding: 12px 12px;
	color: #ffffffff;
	text-decoration: none;
	border-radius: 14px;
	font-size: 18px;
	margin-bottom: 10px;
	transition: background 0.2s, color 0.2s;
	font-weight: 500;
}
.sidebar1 nav a.active,
.sidebar1 nav a:hover {
	background: #f1f5fd;
	color: #325eff;
}

    .sidebar1 .form-toggle {
      background: #00b894;
      color: #fff;
      border: none;
      padding: 10px;
      border-radius: 4px;
      cursor: pointer;
      margin-bottom: 20px;
    }

    .main-dashboard {
      flex: 1;
      padding: 20px;
      overflow-y: auto;
    }

    .banner-section {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .create-btn {
      padding: 10px 16px;
      background-color: #0984e3;
      color: #fff;
      text-decoration: none;
      border-radius: 4px;
    }

    .novel-list ul {
      margin-top: 10px;
      padding-left: 20px;
    }

    .modal-overlay {
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.5);
      display: none;
      justify-content: center;
      align-items: center;
    }

    .login-modal {
      background: #fff;
      padding: 20px;
      border-radius: 8px;
      width: 500px;
      position: relative;
    }

    .close-btn {
      position: absolute;
      top: 10px; right: 10px;
      font-size: 20px;
      background: none;
      border: none;
      cursor: pointer;
    }

    textarea, input, select, button {
      width: 100%;
      padding: 8px;
      margin-top: 8px;
      margin-bottom: 16px;
    }

    .dropdown-tag-label {
      background-color: #ddd;
      padding: 10px;
      cursor: pointer;
    }

    .dropdown-tag-list {
      display: none;
      background: #eee;
      padding: 10px;
      border: 1px solid #ccc;
    }
  </style>
</head>
<body>
  <div class="dashboard-wrapper">
    <!-- SIDEBAR -->
    <aside class="sidebar1">
      <div class="ink-row">
        <a href="../index.php" target="_blank">
          <img src="../image/logoko.png" alt="logo nhỏ" class="logo-right" />
        </a>
      </div>

      <nav>
        <a>📊 Dashboard</a>
        <div class="dropdown">
          <a class="dropdown-btn">👤 <?php echo htmlspecialchars($username); ?></a>
          <div class="dropdown-menu">
            <a href="../components/profile.php">Hồ sơ</a>
            <a href="../auth/logout.php">Đăng xuất</a>
          </div>
        </div>
        <a>🤖 Assistants</a>
      </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-dashboard">
      <div class="banner-section">
        <h2>Dashboard</h2>
        <a href="#" class="create-btn" id="openCreateNovelModal">CREATE NEW</a>
      </div>

      <!-- Quản lý truyện -->
      <div class="novel-list">
        <h3>Truyện đã đăng:</h3>
        <ul>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="novel-card">
            <img src="<?php echo htmlspecialchars($row['cover'] ?? 'images/default.jpg'); ?>" alt="Ảnh bìa">
            <div class="novel-info">
                <div class="novel-title"><?php echo htmlspecialchars($row['title']); ?></div>
                <div class="novel-author">✍️ Tác giả: <?php echo htmlspecialchars($row['author_name']); ?></div>
                <div class="novel-genres">
                    <strong>Thể loại:</strong>
                    <?php
                        $ds_theloai = array_filter(array_merge(
                            [$row['theloai_chinh']],
                            explode(', ', $row['theloai_phu'] ?? '')
                        ));
                        echo htmlspecialchars(implode(', ', $ds_theloai));
                    ?>
                </div>
                <div class="novel-description"><strong>Mô tả: </strong><?php echo nl2br(htmlspecialchars(mb_strimwidth($row['description'], 0, 200, "..."))); ?></div>
                <div class="novel-status <?php echo htmlspecialchars($row['approval']); ?>">
                <?php
                    if ($row['approval'] === 'approved') echo '✅ Đã duyệt';
                    elseif ($row['approval'] === 'pending') echo '⏳ Đang chờ duyệt';
                    else echo '❌ Bị từ chối';
                ?>
                </div>
                <div class="right-btn">
                    <a class="btn btn-chapters" href="add-chap.php?novel_id=<?= $row['novel_id'] ?>">Chương</a>
                </div>
            </div>
            </div>
        <?php endwhile; ?>

        <?php if ($result->num_rows === 0): ?>
            <p class="no-novels">📭 Hiện bạn chưa đăng truyện nào cả.</p>
        <?php endif; ?>
        </ul>
      </div>
    </main>
  </div>

  <!-- MODAL TẠO TRUYỆN -->
  <div id="createNovelModal" class="modal-overlay">
    <div class="login-modal" onclick="event.stopPropagation()">
      <button class="close-btn" onclick="closeCreateNovelModal()">&times;</button>
      <h2>Tạo Truyện Mới</h2>

      <form id="novelForm" enctype="multipart/form-data" action="upload_novel.php" method="post">
        <label>Tên tác giả (để trống nếu là bạn):</label>
        <input type="text" name="tac_gia" placeholder="Nhập tên tác giả">

        <label>Tiêu đề truyện:</label>
        <input type="text" name="ten_truyen" placeholder="Nhập tiêu đề" required>

        <label>Thể loại:</label>
        <select name="the_loai" required>
          <option value="">-- Chọn thể loại --</option>
          <?php
            $stmt = $conn->prepare("SELECT category_id, name FROM categories");
            if ($stmt) {
                $stmt->execute();
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='{$row['category_id']}'>" . htmlspecialchars($row['name']) . "</option>";
                }
                $stmt->close();
            }
          ?>
        </select>

        <div class="dropdown-tag">
          <div class="dropdown-tag-label" onclick="toggleTagDropdown()">
            Tag (bấm để chọn) <i>▼</i>
          </div>
          <div class="dropdown-tag-list" id="tagList">
            <?php
              $stmt = $conn->prepare("SELECT tag_id, name FROM tags");
              if ($stmt) {
                  $stmt->execute();
                  $result = $stmt->get_result();
                  while ($row = $result->fetch_assoc()) {
                      echo "<label><input type='checkbox' name='tags[]' value='{$row['tag_id']}'> " . htmlspecialchars($row['name']) . "</label><br>";
                  }
                  $stmt->close();
              }
            ?>
          </div>
        </div>

        <label>Ảnh bìa (tuỳ chọn):</label>
        <input type="file" name="cover" accept="image/*">

        <label>Mô tả:</label>
        <textarea name="tom_tat" rows="4" placeholder="Nhập tóm tắt nội dung"></textarea>

        <button type="submit">Đăng Truyện</button>
      </form>
    </div>
  </div>

    <script type="text/javascript" src="../js/dangnhap.js"></script>

    <script type="text/javascript" src="../js/imgcover.js"></script>    
    <script type="text/javascript" src="../js/create.js?v=<?php echo time(); ?>"></script>
</body>
</html>
