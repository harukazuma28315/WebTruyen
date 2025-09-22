<?php
include '../db_connect.php'; // Kết nối đến cơ sở dữ liệu

// Nếu chưa có session thì khởi tạo lại (phòng ngừa)
if (session_status() === PHP_SESSION_NONE) session_start();



// Lấy danh sách categories (loại truyện chính)
$sql_categories = "SELECT * FROM categories";
$result_categories = $conn->query($sql_categories);

// Lấy danh sách tags (thể loại phụ)
$sql_tags = "SELECT * FROM tags";
$result_tags = $conn->query($sql_tags);

// Gom tag thành từng nhóm để hiển thị chia cột
$tags = [];
while ($tag = $result_tags->fetch_assoc()) {
    $tags[] = $tag;
}
// Chia tag ra mỗi cột 7 tag
$tag_columns = array_chunk($tags, ceil(count($tags) / 2));
?>
<?php

if (!isset($_SESSION['user_id'])) {
    // Lưu lại trang bị đá ra để quay về sau khi đăng nhập
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ../index/index.php?login=1');
    exit();
}

// Lấy thông tin người dùng từ session
$username = $_SESSION['name'];
$avatar = $_SESSION['avatar'];
?>
<!DOCTYPE html>
<html lang="vi">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Web Truyện Của PTKPY</title>
    <link rel="stylesheet" href="../css/thongtin.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="../css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/dangnhap.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/theloai.css?v=<?php echo time(); ?>">
   <link rel="stylesheet" href="../css/search.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
      <style>
    .library-wrapper {
      width: 100%;
      max-width: 1450px;
      margin: 100px auto 0 auto;
      padding: 50px 20px;
      background-color: #f9f9f9;
      border-radius: 12px;
      box-shadow: 0 0 15px rgba(0,0,0,0.1);
    }
    .library-wrapper h1 {
      font-size: 42px;
      margin-bottom: 12px;
      font-weight: bold;
      color: #222;
    }
    .library-tabs {
      display: flex;
      justify-content: center;
      margin-bottom: 40px;
      gap: 30px;
      font-size: 20px;
    }
    .library-tabs a {
      text-decoration: none;
      color: #666;
      font-weight: 500;
      padding-bottom: 4px;
      border-bottom: 3px solid transparent;
    }
    .library-tabs a.active {
      color: #222;
      font-weight: bold;
      border-color: #4a4ac7;
    }
    .library-tabs a:hover {
    color: #222;
    border-color: #ccc;
    transform: translateY(-2px);
    }
    .tab-content a{
      text-decoration: none;
    }
    .empty-library {
      text-align: center;
      margin-top: 60px;
      color: #666;
      font-size: 18px;
    }
    .empty-library img {
      width: 120px;
      height: auto;
      opacity: 0.7;
      margin-bottom: 20px;
      max-width: 180px;
    }
    .empty-library a {
      color: #3366cc;
      text-decoration: none;
      font-weight: 500;
    }
    .empty-library a:hover {
      text-decoration: underline;
    }

    .empty-library p {
      font-size: 18px;
      color: #555;
    }

    button {
      background: #f56565;
      color: white;
      padding: 5px 10px;
      border-radius: 6px;
      cursor: pointer;
    }

    button:hover {
      background: #c53030;
    }

  </style>
  </head>
  <body>
    <div class="Big_page">
    <div class="header">
        <a href="../index/index.php"><img src="../image/logoko.png" alt="logo" id="imglogo"></a>
        <div class="navbar">
            <a href="../index/index.php"><i class="fa fa-home"></i> Trang Chủ</a>
            <div class="menu-item">
            <a href=""><i class="fa fa-bars"></i> Thể Loại</a>
              <div class="mega-menu">
                <div class="left-menu">
                    <?php
                    $sql_categories = "SELECT * FROM categories";
                    $result_categories = $conn->query($sql_categories);
                    $categories = [];
                    $index = 0;
                    while ($row = $result_categories->fetch_assoc()) {
                        $categories[] = $row;
                        echo '<a href="../novels/theloai.php?id=' . $row['category_id'] . '&type=category" class="' . ($index === 0 ? 'active' : '') . '" data-target="' . $index . '">' . htmlspecialchars($row['name']) . '</a>';
                        $index++;
                    }
                    ?>
                </div>
                    <?php for ($i = 0; $i < count($categories); $i++): ?>
                        <div class="right-content <?= $i === 0 ? 'show' : '' ?>" id="content-<?= $i ?>">
                            <?php foreach ($tag_columns as $column): ?>
                                <div class="right2-column">
                                    <ul>
                                        <?php foreach ($column as $tag): ?>
                                            <li>
                                                <a href="../novels/theloai.php?id=<?= $tag['tag_id'] ?>&type=tag">
                                                    <?= htmlspecialchars($tag['name']) ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endfor; ?>
                </div>
              </div>
            <a href="../novels/xephang.php"><i class="fa fa-flag"></i> Xếp Hạng</a>
             <a href="../novels/create.php" target="_blank"><i class="fa fa-pen"></i> Tạo Mới</a>
            
            <a href="../novels/library.php"><i class="fa fa-book"></i> Thư Viện</a>
        </div>
        <div class="header-right">
            <div class="search-bar">
                <input type="text" id="search-input" placeholder="Tìm kiếm...">
                <button id="search-btn"><i class="fa fa-search"></i></button>
                 <ul id="search-results"></ul>
            </div>
            <ul id="search-results"></ul>
            <div id="dangnhap">
  <?php include_once '../components/user_avatar_button.php'; ?>
</div>
<div class="modal-overlay" id="registerModal" onclick="closeRegisterModal(event)">
      <div class="login-modal" onclick="event.stopPropagation()">
          <button class="close-btn" onclick="closeRegisterModal()">&times;</button>

          <div class="modal-header">
          <img src="../image/logoko.png" alt="logo" id="imglogo">
          <h2 class="modal-title">Đăng ký tài khoản</h2>
          <p class="modal-subtitle">Tham gia Web Lore và khám phá kho truyện hấp dẫn!</p>
          </div>

          <form class="input-login" method="POST" action="register.php">
          <input type="text" name="name" placeholder="Tên đăng nhập">
          <input type="email" name="email" placeholder="Email">
          <input type="password" name="password" placeholder="Mật khẩu">
          <input type="password" name="confirm_password" placeholder="Xác nhận mật khẩu">
          <button type="submit">ĐĂNG KÝ</button>
          </form>

          <div style="text-align: center; margin-top: 20px;">
          <a href="#" id="backToLogin" style="color: #667eea; text-decoration: none; font-weight: 500;">Đã có tài khoản? Đăng nhập</a>
          </div>
      </div>
  </div>
    </div>
      </div>
      
          <!-- LIBRARY UI -->
    <div class="library-wrapper">
      <h1  style='text-align: center;'>Thư Viện</h1>
      <div class="library-tabs">
        <a href="#" class="tab-link active" data-tab="library">Thư Viện</a>
        <a href="#" class="tab-link" data-tab="history">Lịch Sử</a>
      </div>
      <div id="library-tab" class="tab-content" >
        <?php
            if (isset($_SESSION['user_id'])) {
                $user_id = $_SESSION['user_id'];

                // Xử lý nút xoá truyện khỏi thư viện
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_library_id'])) {
                $delete_id = intval($_POST['delete_library_id']);
                $sql_delete = "DELETE FROM user_library WHERE user_id = $user_id AND novel_id = $delete_id";
                mysqli_query($conn, $sql_delete);
            }

                $sql = "
                    SELECT n.novel_id, n.title, n.cover, c.name AS category_name
                    FROM user_library ul
                    JOIN novels n ON ul.novel_id = n.novel_id
                    LEFT JOIN categories c ON n.category_id = c.category_id
                    WHERE ul.user_id = $user_id
                    ORDER BY ul.added_at DESC
                ";

                $result = mysqli_query($conn, $sql);
                if ($result && mysqli_num_rows($result) > 0) {
                    echo "<h2>📚 Thư Viện Của Bạn</h2><hr>";
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<div style='margin-bottom:20px; padding:10px; border:1px solid #ccc; border-radius:8px; display:flex; gap:15px; align-items:center;'>";
                        echo "<img src='" . $row['cover'] . "' alt='cover' width='100' height='140' style='object-fit:cover; border-radius:4px;'>";
                        echo "<div>";
                        echo "<h3 style='margin:0;'>" . htmlspecialchars($row['title']) . "</h3>";
                        echo "<p>Thể loại: " . htmlspecialchars($row['category_name']) . "</p>";
                        echo "<a href='thongtin.php?id=" . $row['novel_id'] . "' style='text-decoration: none'>📖 Xem truyện</a>";
                        echo "<form method='post' action='' style='display:inline;'>
                        <input type='hidden' name='delete_library_id' value='" . $row['novel_id'] . "'>
                        <button type='submit' onclick=\"return confirm('Xoá truyện này khỏi thư viện?')\">🗑️ Xoá</button>
                      </form>";
                        echo "</div></div>";
                    }
                } else {
                    echo "<p style='text-align: center;'>⚠️ Bạn chưa thêm truyện nào vào thư viện.</p>";
                }
            } else {
                echo "<p style='text-align: center;'>Vui lòng đăng nhập để xem thư viện.</p>";
            }
          ?>
        <?php if (!($result && mysqli_num_rows($result) > 0)) : ?>
            <div class="empty-library">
              <img src="../image/book.png" alt="Empty Library" />
              <p>Bạn vẫn chưa thêm bất kỳ truyện nào!!!<br>Đã đến lúc để <a href="../index/index.php">KHÁM PHÁ</a></p>
            </div>
        <?php endif; ?>
      </div>
      <div id="history-tab" class="tab-content" style="display: none;">
<?php 
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];

        $sql = "
            SELECT n.novel_id, n.title, n.cover, n.category_id, c.title AS chapter_title, c.number AS chapter_number, h.last_read_at
            FROM reading_history h
            JOIN novels n ON h.novel_id = n.novel_id
            LEFT JOIN chapters c ON h.chapter_id = c.chapter_id
            WHERE h.user_id = $user_id
            ORDER BY h.last_read_at DESC
        ";

        $result = mysqli_query($conn, $sql);
        if ($result && mysqli_num_rows($result) > 0) {
            echo "<h2 style='font-family:sans-serif;'>📖 Lịch sử đọc gần đây</h2><hr>";
            while ($row = mysqli_fetch_assoc($result)) {
                $chap_number = $row['chapter_number'] ?? 1;
                echo "<div class='history-item' id='history-{$row['novel_id']}' style='margin-bottom:20px; padding:10px; border:1px solid #ccc; border-radius:8px; display:flex; gap:15px; align-items:center;'>";
                echo "<img src='" . $row['cover'] . "' alt='cover' width='100' height='140' style='object-fit:cover; border-radius:4px;'>";
                echo "<div>";
                echo "<h3 style='margin:0;'>" . $row['title'] . "</h3>";
                echo "<p>Chương gần nhất: <strong>" . ($row['chapter_title'] ?? 'Chưa có chương') . "</strong></p>";
                echo "<p>Lần đọc cuối: " . $row['last_read_at'] . "</p>";

                $link = ($row['category_id'] == 2)
                    ? "chapimages.php?truyen=" . $row['novel_id'] . "&chap=" . $chap_number
                    : "chap.php?truyen=" . $row['novel_id'] . "&chap=" . $chap_number;

                echo "<a href='" . $link . "' style='text-decoration: none'>📘 Đọc tiếp</a> ";
                echo "<button class='delete-history' data-id='" . $row['novel_id'] . "' style='margin-left:10px;'>🗑️ Xoá</button>";
                echo "</div></div>";
            }
        } else {
            echo "<p style='text-align: center;'>⚠️ Bạn chưa đọc truyện nào.</p>";
        }
    } else {
        echo "<p style='text-align: center;'>Vui lòng đăng nhập để xem lịch sử đọc.</p>";
    }
?>
<div class="empty-library" style="<?php echo ($result && mysqli_num_rows($result) > 0) ? 'display: none;' : ''; ?>">
  <img src="../image/history.png" alt="Empty History" />
  <p>Bạn chưa đọc truyện nào.<br>Quay lại và <a href="../index/index.php">khám phá</a> những truyện mới.</p>
</div>
</div>

    </div>
    </div>
    <footer class="footer">
  <div class="footer-container">
    <div class="footer-linkss">
      <div class="column">
        <div class="footer-logo">
      <img src="../image/logo.png" alt="Web Logo" class="logo">
    </div>
        <h4>TEAM</h4>
        <ul>
          <li><a href="#">Về</a></li>
          <li><a href="#">Tin tức</a></li>
          <li><a href="#">Phương châm thương hiệu</a></li>
          <li>
            <div class="social-icons">
                <a href="#"><i class="fa-brands fa-square-facebook fa-2xl" style="color: #ffffffff;"></i></a>
                <a href="#"><i class="fa-brands fa-square-twitter fa-2xl" style="color: #ffffffff;"></i></a>
                <a href="#"><i class="fa-brands fa-instagram fa-2xl" style="color: #ffffffff;"></i></a>
                <a href="#"><i class="fa-brands fa-tiktok fa-xl" style="color: #ffffffff;"></i></a>
                <a href="#"><i class="fa-brands fa-youtube fa-2xl" style="color: #ffffffff;"></i></a>
            </div>
          </li>
        </ul>
      </div>
      <div class="column">
        <h4>CONTACTS</h4>
        <ul>
          <li><a href="#">Dịch giả & Biên tập viên</a></li>
          <li><a href="#">Thương mại</a></li>
          <li><a href="#">Kinh doanh âm thanh</a></li>
          <li><a href="#">Trợ giúp & Dịch vụ</a></li>
          <li><a href="#">Thông báo DMCA</a></li>
          <li><a href="#">Dịch vụ trực tuyến</a></li>
          <li><a href="#">Báo cáo lỗi hỏng</a></li>
        </ul>
      </div>
      <div class="column">
        <h4>TÀI NGUYÊN</h4>
        <ul>
          <li><a href="#">Tải xuống ứng dụng</a></li>
          <li><a href="#">Hãy là một tác giả</a></li>
          <li><a href="#">Trung tâm trợ giúp</a></li>
          <li><a href="#">Chính sách quyền riêng tư</a></li>
          <li><a href="#">Điều khoản dịch vụ</a></li>
          <li><a href="#">Liên kết</a></li>
        </ul>
      </div>
    </div>
  </div>
  <div class="footer-bottom">
    <p>© 2025 Cloudary Holdings Limited</p>
  </div>
</div>

</footer>

    <div class="modal-overlay" id="loginModal" onclick="closeLoginModal(event)">
      <div class="login-modal" onclick="event.stopPropagation()">
          <button class="close-btn" onclick="closeLoginModal(event)">&times;</button>

          <div class="modal-header">
          <img src="../image/logoko.png" alt="logo" id="imglogo">
          <h2 class="modal-title">Chào mừng bạn đến với Web lore</h2>
          <p class="modal-subtitle">Truy cập vào rất nhiều tiểu thuyết và truyện tranh chỉ bằng một cú nhấp</p>
          </div>

          <form class="input-login" method="POST" action="../auth/login.php">
          <input type="text" name="login_name" placeholder="Tên đăng nhập">
          <input type="password" name="login_password" placeholder="Mật khẩu">
          <button type="submit">ĐĂNG NHẬP</button>
          </form>

          <div class="divider">
          <span>HOẶC</span>
          </div>

          <div class="social-login">
          <div class="social-login">
              <div class="social-btn-circle" title="Google">
              <img src="../image/google.png" alt="Google" style="width: 24px; height: 24px;">
              </div>
              <div class="social-btn-circle" title="Facebook">
              <img src="../image/facebook.png" alt="Facebook" style="width: 24px; height: 24px;">
              </div>
          </div>
          </div>

          <div style="text-align: center; margin-top: 20px;">
          <a href="#" id="openRegister" style="color: #667eea; text-decoration: none; font-weight: 500;">TẠO TÀI KHOẢN</a>
          </div>

          <div class="footer-links">
          © 2025 Web Lore | <a href="#">Điều khoản dịch vụ</a> | <a href="#">Chính sách bảo mật</a>
          </div>
  <script type="text/javascript" src="../js/dangnhap.js" ></script>
    <script type="text/javascript" src="../js/main.js" ></script>
    <script type="text/javascript" src="../js/theloai.js" ></script>
    <script type="text/javascript" src="../js/search.js" ></script>  
    <script type="text/javascript" src="../js/library.js" ></script>
    <script type="text/javascript" src="../js/dlhs.js" ></script>
  </body>
</html>