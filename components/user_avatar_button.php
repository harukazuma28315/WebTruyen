<?php
// Kết nối database
$conn = new mysqli("localhost", "root", "", "webnovel");
$conn->set_charset("utf8mb4");
?>
<?php if (isset($_SESSION['user_id'])): ?>
  <!-- Đã đăng nhập: hiện avatar + dropdown menu -->
  <?php
    echo '<!-- avatar: ' . htmlspecialchars($_SESSION['avatar'] ?? 'NULL') . ' -->';
    $avatar = htmlspecialchars($_SESSION['avatar'] ?? 'default-avatar.png');
    $username = htmlspecialchars($_SESSION['name'] ?? 'User');
  ?>
  <link rel="stylesheet" href="../css/user_avatar_button.css">

  <div class="user-menu-wrapper" id="userMenuWrapper">
    <!-- Avatar - click vào sẽ hiện menu -->
    <span class="user-avatar-btn" id="userAvatarBtn">
      <img src="../image/avatars/<?php echo $avatar; ?>" alt="User">
    </span>
    <!-- Dropdown menu user -->
    <div class="user-dropdown" id="userDropdown">
      <div class="user-name"><?php echo $username; ?></div>
      <button onclick="window.location.href='../components/profile.php'">Hồ sơ</button>
      <form method="post" action="../auth/logout.php" style="margin:0;">
        <button type="submit">Đăng xuất</button>
      </form>
    </div>
  </div>
  <script>
  document.addEventListener('DOMContentLoaded', function(){
    const btn = document.getElementById('userAvatarBtn');
    const wrapper = document.getElementById('userMenuWrapper');
    document.addEventListener('click', function(e){
      // Bấm vào avatar thì hiện menu, bấm ra ngoài thì ẩn
      if (btn.contains(e.target)) {
        wrapper.classList.toggle('show');
      } else if (!wrapper.contains(e.target)) {
        wrapper.classList.remove('show');
      }
    });
  });
  </script>
<?php else: ?>
  <!-- Chưa đăng nhập: hiện nút đăng nhập -->
  <a href="#" id="dangnhap" onclick="openLoginModal(); return false;">
  <i class="fa-solid fa-user  fa-2xl"></i>
  <span class="login-btn">ĐĂNG NHẬP</span>
</a>

<?php endif; ?>
