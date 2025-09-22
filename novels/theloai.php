<?php
session_start();
$conn = new mysqli("localhost", "root", "", "webnovel");

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// ✅ Lấy và kiểm tra tham số truyền vào
if (!isset($_GET['id']) || !isset($_GET['type'])) {
    echo "Thiếu thông tin thể loại.";
    exit;
}

$id = intval($_GET['id']);
$type = $_GET['type'];

// ✅ Lấy tên thể loại
if ($type === 'category') {
    $sql = "SELECT name FROM categories WHERE category_id = ?";
} elseif ($type === 'tag') {
    $sql = "SELECT name FROM tags WHERE tag_id = ?";
} else {
    echo "Loại thể loại không hợp lệ.";
    exit;
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Không tìm thấy thể loại.";
    exit;
}

$row = $result->fetch_assoc();
$category_name = $row['name'];

// ✅ Lấy danh sách truyện theo loại thể loại
if ($type === 'category') {
    $sql_novels = "SELECT * FROM novels WHERE category_id = ?";
} elseif ($type === 'tag') {
    $sql_novels = "
        SELECT n.*
        FROM novels n
        JOIN novel_tag nt ON n.novel_id = nt.novel_id
        WHERE nt.tag_id = ?
    ";
}

$stmt_novels = $conn->prepare($sql_novels);
$stmt_novels->bind_param("i", $id);
$stmt_novels->execute();
$result_novels = $stmt_novels->get_result();


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

<!DOCTYPE html>
<html lang="vi">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Web Truyện Của PTKPY</title>
    <link rel="stylesheet" href="../thongtin/thongtin.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="../css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/dangnhap.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/theloai.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/search.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <style>
            /* Wrapper */
/* Wrapper */
.novel-list-wrapper {
    max-width: 1200px;
    margin: 40px auto;
    padding: 20px;
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 122, 204, 0.1);
}

/* Tiêu đề */
.novel-list-title {
    font-size: 28px;
    text-align: center;
    color: #007acc;
    margin-bottom: 30px;
}

/* Danh sách: Dạng Grid */
.novel-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); /* 👈 nhỏ hơn */
    gap: 20px;
    justify-items: center;
}

/* Mỗi ô truyện */
.novel-item {
    background-color: #f0f8ff;
    border-radius: 10px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 6px rgba(0, 122, 204, 0.08);
    padding: 10px;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100%;           /* full trong ô grid */
    max-width: 120px;      /* 👈 giới hạn chiều rộng */
    box-sizing: border-box;
}

.novel-item:hover {
    transform: translateY(-4px);
    box-shadow: 0 6px 14px rgba(0, 122, 204, 0.15);
}

/* Link */
.novel-link {
    text-decoration: none;
    color: inherit;
    display: block;
    width: 100%;
}

/* Ảnh bìa */
.novel-cover {
    width: 100px;
    height: 133px;
    object-fit: cover;
    border-radius: 6px;
    margin-bottom: 10px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
}

/* Tiêu đề truyện */
.novel-title {
    font-size: 13px;
    font-weight: 600;
    color: #005fa3;
    text-align: center;
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;        /* 👈 chỉ cho hiển thị 2 dòng */
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    height: 2.6em;                 /* 👈 đúng 2 dòng */
    width: 100%;
}


/* Không có truyện */
.novel-empty {
    font-size: 16px;
    color: #777;
    text-align: center;
    padding: 30px;
}

/* Nút xem thêm */
.show-more-btn {
    display: block;
    margin: 30px auto 0;
    background-color: #007acc;
    color: white;
    padding: 10px 24px;
    font-size: 14px;
    font-weight: bold;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.3s ease;
}

.show-more-btn:hover {
    background-color: #005fa3;
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
      
      <div class="novel-list-wrapper">
    <h2 class="novel-list-title">📚 Danh sách truyện thể loại: <?php echo htmlspecialchars($category_name); ?></h2>

    <?php
    if ($result_novels->num_rows === 0) {
        echo '<p class="novel-empty">Không có truyện nào trong thể loại này.</p>';
    } else {
        echo '<ul class="novel-list">';
        while ($novel = $result_novels->fetch_assoc()) {
            echo '<li class="novel-item">';
            echo '<a class="novel-link" href="thongtin.php?id=' . $novel['novel_id'] . '">';
            echo '<img class="novel-cover" src="' . htmlspecialchars($novel['cover']) . '" alt="cover">';
            echo '<span class="novel-title">' . htmlspecialchars($novel['title']) . '</span>';
            echo '</a>';

            // Tags (optional – chưa in ra ở đây nhưng có thể thêm nếu muốn)
            echo '</li>';
        }
        echo '</ul>';
    }
    ?>
</div>
<button class="show-more-btn" onclick="showMore(this)">XEM THÊM</button>

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
  </footer>
  <script type="text/javascript" src="../js/dangnhap.js" ></script>
    <script type="text/javascript" src="../js/main.js" ></script>
    <script type="text/javascript" src="../js/theloai.js" ></script>
    <script type="text/javascript" src="../js/search.js" ></script>
  
  </body>
  
</html>