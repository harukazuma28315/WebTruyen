<?php
session_start();
include '../db_connect.php';


if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ../auth/login1_from.php');
    exit();
}
$username = $_SESSION['name'];

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CREATE</title>
    <link rel="stylesheet" href="../css/create.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/create_user.css?v=<?php echo time(); ?>">

    <link rel="stylesheet" href="../css/theloai.css?v=<?php echo time(); ?>">
   
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .preview-img {
          max-width: 150px;
          margin-top: 8px;
          border: 1px solid #ccc;
          border-radius: 6px;
          box-shadow: 0 0 4px rgba(0,0,0,0.1);
        }
</style>
</head>
<body>
    
    <div class="dashboard-wrapper">
    <!-- SIDEBAR -->
    <aside class="sidebar1">
    <div class="ink-row">
        <a href="../index.php" target="_blank"><img src="../image/logoko.png" alt="logo nhỏ" class="logo-right" /></a>
    </div>

            <nav>
                <a class="">📊 Dashboard</a>
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
                <div class="banner1">
                    <a href="#" class="create-btn" id="openCreateNovelModal">CREATE NEW</a>
                </div>  
            </div>
            
        </main>
    </div>

<div id="createNovelModal" class="modal-overlay" style="display:none;">
  <div class="login-modal" onclick="event.stopPropagation()">
    <button class="close-btn" onclick="closeCreateNovelModal()">&times;</button>
    <h2>Tạo Truyện Mới</h2>
    
    <form id="novelForm" enctype="multipart/form-data" action="upload_novel.php" method="post">
      <script>
        document.getElementById('novelForm').addEventListener('submit', function (e) {
          const title = this.ten_truyen.value.trim();
          const author = this.tac_gia.value.trim();
          const category = this.the_loai.value;

          if (!title || !category) {
            e.preventDefault();
            alert("❌ Vui lòng điền đầy đủ các trường bắt buộc!");
          }
        });
      </script>

      <label>Tên tác giả:(Hãy bỏ trống nếu truyện do bạn sáng tác)</label>
      <input type="text" name="tac_gia" placeholder="Nhập tên tác giả">

      <label>Tiêu đề truyện:</label>
      <input type="text" name="ten_truyen" placeholder="Nhập tiêu đề" required>

      <label>Thể loại:</label>
      <select name="the_loai" required>
        <option value="">-- Chọn thể loại --</option>
        <?php
          include '../db_connect.php';
          $stmt = $conn->prepare("SELECT category_id, name FROM categories");
          $stmt->execute();
          $result = $stmt->get_result();
          foreach ($result as $row) {
              echo "<option value='" . $row['category_id'] . "'>" . htmlspecialchars($row['name']) . "</option>";
          }
          $stmt->close();
        ?>
      </select>

      <div class="dropdown-tag">
        <div class="dropdown-tag-label" onclick="toggleTagDropdown()">
          Tag (bấm để chọn) <i>▼</i>
        </div>

        <div class="dropdown-tag-list" id="tagList" style="display:none;">
          <div>
            <?php
              $stmt = $conn->prepare("SELECT tag_id, name FROM tags");
              $stmt->execute();
              $result = $stmt->get_result();
              foreach ($result as $row) {
                  $tagName = htmlspecialchars($row['name']);
                  echo "<label><input type='checkbox' name='tags[]' value='{$row['tag_id']}'> $tagName</label>";
              }
              $stmt->close();
            ?>
          </div>
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

<script>
  function toggleTagDropdown() {
    const list = document.getElementById('tagList');
    list.style.display = (list.style.display === 'none') ? 'block' : 'none';
  }
</script>

    <script type="text/javascript" src="../js/dangnhap.js"></script>
    
    <script type="text/javascript" src="../js/theloai.js"></script>
     
    <script type="text/javascript" src="../js/create.js"></script>
    <script type="text/javascript" src="../js/imgcover.js"></script>
</body>
</html>