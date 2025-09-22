<?php
session_start();
?>
<?php
include '../db_connect.php'; // Kết nối đến cơ sở dữ liệu

// Nếu chưa có session thì khởi tạo lại (phòng ngừa)
if (session_status() === PHP_SESSION_NONE) session_start();

// Hàm lấy danh sách truyện theo category
function layDanhSachTruyen($conn, $limit = 10, $offset = 0, $category_id = null, $show_all = false) {
    $category_filter = is_null($category_id) ? '1' : "n.category_id = " . intval($category_id);
    $approval_filter = $show_all
    ? "n.approval IN ('approved', 'pending')"  // admin thấy approved + pending
    : "n.approval = 'approved'";               // người dùng chỉ thấy approved


    $sql = "
    SELECT 
        n.novel_id,
        n.title,
        n.cover,
        n.rating,
        c.name AS theloai_chinh,
        GROUP_CONCAT(t.name SEPARATOR ', ') AS theloai_phu
    FROM novels n
    LEFT JOIN categories c ON n.category_id = c.category_id
    LEFT JOIN novel_tag nt ON n.novel_id = nt.novel_id
    LEFT JOIN tags t ON nt.tag_id = t.tag_id
    WHERE $category_filter AND $approval_filter
    GROUP BY n.novel_id
    ORDER BY n.rating DESC
    LIMIT $limit OFFSET $offset
    ";
    
    $ds = [];
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $ds[] = [
                'novel_id' => $row['novel_id'],
                'ten' => $row['title'],
                'anh' => $row['cover'],
                'danhgia' => $row['rating'],
                'theloai' => array_filter(array_merge(
                    [$row['theloai_chinh']],
                    explode(', ', $row['theloai_phu'] ?? '')
                ))
            ];

        }
    }
    return $ds;
}
$show_all = isset($_SESSION['level']) && $_SESSION['level'] == 1;

$group_all = layDanhSachTruyen($conn, 10, 0, null, $show_all); // Hiển thị tất cả truyện



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
    <link rel="stylesheet" href="../css/xephang.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/dangnhap.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/theloai.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/search.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
      
      <div class="main">
      <!-- Winner -->
       <div class="sidebar">
        <p class="ranking-title">Theo Mùa</p>
        <ul class="sidebar-list">
          <?php foreach ($group_all as $truyen): 
            $cover = $truyen['anh'];?>
            <li class="box-truyen">
              <a href="../novels/thongtin.php?id=<?php echo $truyen['novel_id']; ?>">
                <div class="truyen-wrapper">
                  <img class="cover-img" src="<?php echo $truyen['anh']; ?>" alt="<?php echo $truyen['ten']; ?>" />
                  <div class="info-truyen">
                    <div class="title-ranking"><?php echo $truyen['ten']; ?></div>
                    <div class="genre-ranking"><?php echo htmlspecialchars(implode(', ', $truyen['theloai'])); ?></div>
                    <span id="rating">★ <?php echo htmlspecialchars($truyen['danhgia']); ?></span>
                  </div>
                </div>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <!-- Product -->
      <div class="sidebar">
        <p class="ranking-title">Theo số lượng đọc</p>
        <ul class="sidebar-list">
          <?php foreach ($group_all as $truyen): 
            $cover = $truyen['anh'];?>
            <li class="box-truyen">
              <a href="../novels/thongtin.php?id=<?php echo $truyen['novel_id']; ?>">
                <div class="truyen-wrapper">
                  <img class="cover-img" src="<?php echo $truyen['anh']; ?>" alt="<?php echo $truyen['ten']; ?>" />
                  <div class="info-truyen">
                    <div class="title-ranking"><?php echo $truyen['ten']; ?></div>
                    <div class="genre-ranking"><?php echo htmlspecialchars(implode(', ', $truyen['theloai'])); ?></div>
                    <span id="rating">★ <?php echo htmlspecialchars($truyen['danhgia']); ?></span>
                  </div>
                </div>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <!-- Power -->
<div class="sidebar">
  <p class="ranking-title">Theo Tag thịnh hành</p>
  <ul class="sidebar-list">
    <?php foreach ($group_all as $truyen): ?>
      <li class="box-truyen">
        <a href="../novels/thongtin.php?id=<?php echo $truyen['novel_id']; ?>">
          <div class="truyen-wrapper">
            <img class="cover-img" src="<?php echo $truyen['anh']; ?>" alt="<?php echo $truyen['ten']; ?>" />
            <div class="info-truyen">
              <div class="title-ranking"><?php echo $truyen['ten']; ?></div>
              <div class="genre-ranking"><?php echo htmlspecialchars(implode(', ', $truyen['theloai'])); ?></div>
              <span id="rating">★ <?php echo htmlspecialchars($truyen['danhgia']); ?></span>
            </div>
          </div>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
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
  </body>
</html>