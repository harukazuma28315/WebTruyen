<?php
include '../db_connect.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Đăng nhập Webnovel</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      padding: 0;
      min-height: 100vh;
      font-family: 'Montserrat', Arial, sans-serif;
      background: #edefff;
      overflow-x: hidden;
    }
    .modal-overlay {
      position: fixed;
      z-index: 1000;
      inset: 0;
      background: rgba(42,46,81,0.15);
      display: flex;
      align-items: center;
      justify-content: center;
      transition: background 0.3s;
      min-height: 100vh;
      min-width: 100vw;
      animation: fadein 1s;
    }
    .modal-overlay:not(.show) {
      display: none;
    }
    .login-modal {
      background: #fff;
      border-radius: 22px;
      box-shadow: 0 10px 40px #b9c4ff3d;
      padding: 44px 38px 38px 38px;
      width: 370px;
      text-align: center;
      animation: popin 0.7s;
      position: relative;
    }
    @keyframes fadein {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    @keyframes popin {
      from { transform: scale(.95) translateY(40px); opacity: 0;}
      to { transform: none; opacity: 1;}
    }
    .modal-header .modal-logo {
      font-size: 43px;
      font-weight: bold;
      color: #6a6cef;
      letter-spacing: 2px;
      text-shadow: 0 2px 16px #8799ff38;
      margin-bottom: 9px;
    }
    .modal-header .modal-title {
      font-size: 1.32rem;
      font-weight: 700;
      color: #23255c;
      margin-bottom: 6px;
      letter-spacing: .1px;
    }
    .modal-header .modal-subtitle {
      color: #8a90a8;
      font-size: 1.01rem;
      margin-bottom: 20px;
      font-weight: 500;
    }
    .input-login .form-floating {
      position: relative;
      margin-bottom: 23px;
      text-align: left;
    }
    .input-login input {
      width: 100%;
      padding: 15px 14px 15px 16px;
      border: 1.5px solid #e2e7fa;
      border-radius: 9px;
      font-size: 1.07rem;
      outline: none;
      background: #fafdff;
      font-family: 'Montserrat', Arial, sans-serif;
      font-weight: 500;
      transition: border 0.21s, background 0.21s;
      box-shadow: 0 2px 12px #e5ebff0d;
    }
    .input-login input:focus {
      border: 1.5px solid #6a6cef;
      background: #f4f5fe;
    }
    .input-login label {
      position: absolute;
      left: 16px;
      top: 15px;
      font-size: 1.06rem;
      color: #9aa5cb;
      background: transparent;
      pointer-events: none;
      transition: 0.22s cubic-bezier(.77,.2,.41,1.01);
      padding: 0 6px;
      border-radius: 6px;
    }
    .input-login input:focus + label,
    .input-login input:not(:placeholder-shown) + label {
      top: -9px;
      left: 10px;
      font-size: .96rem;
      color: #6a6cef;
      background: #fff;
      padding: 0 5px;
      box-shadow: 0 0 4px #dbdbff11;
    }
    .input-login button {
      width: 100%;
      padding: 14px 0;
      background: linear-gradient(92deg,#6a6cef 60%, #88a3fa 100%);
      color: #fff;
      border: none;
      border-radius: 8px;
      font-size: 1.09rem;
      font-weight: 700;
      margin-bottom: 8px;
      margin-top: 6px;
      letter-spacing: .5px;
      box-shadow: 0 2px 13px #6a6cef29;
      cursor: pointer;
      transition: background .23s, transform 0.16s, box-shadow 0.18s;
    }
    .input-login button:hover, .input-login button:focus {
      background: linear-gradient(98deg,#4c5ace 70%, #88a3fa 100%);
      transform: translateY(-2px) scale(1.02);
      box-shadow: 0 8px 18px #6a6cef45;
    }
    .divider {
      margin: 20px 0 14px 0;
      text-align: center;
      color: #aaa;
      font-size: .97rem;
      font-weight: 600;
      position: relative;
      letter-spacing: 0.04em;
    }
    .divider span {
      background: #fff;
      padding: 0 14px;
      position: relative;
      z-index: 2;
    }
    .divider:before {
      content: '';
      display: block;
      height: 1px;
      width: 100%;
      background: #ecebff;
      position: absolute;
      top: 50%;
      left: 0;
      z-index: 1;
    }
    .social-login {
      display: flex;
      justify-content: center;
      gap: 18px;
      margin-bottom: 10px;
    }
    .social-btn-circle {
      width: 42px;
      height: 42px;
      background: #f1f2fd;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #6a6cef;
      font-size: 1.15rem;
      font-weight: 800;
      box-shadow: 0 1px 5px #c2c5e025;
      cursor: pointer;
      transition: background 0.18s, box-shadow 0.19s, color 0.17s;
      user-select: none;
    }
    .social-btn-circle:hover {
      background: #6a6cef;
      color: #fff;
      box-shadow: 0 2px 9px #6872e74c;
      transform: scale(1.09);
    }
    .footer-links {
      margin-top: 14px;
      font-size: 1.01rem;
      color: #7a7f8c;
      font-weight: 500;
      letter-spacing: 0.08px;
      user-select: none;
    }
    .footer-links a {
      color: #6a6cef;
      text-decoration: none;
      font-weight: 700;
      transition: color 0.16s;
    }
    .footer-links a:hover {
      color: #23255c;
      text-decoration: underline;
    }
    @media (max-width: 600px) {
      .login-modal { width: 99vw; border-radius: 0; padding: 24px 2vw; }
    }
  </style>
</head>
<body>
  <!-- Modal đăng nhập (luôn hiển thị khi mở trang) -->
  <div id="loginModal" class="modal-overlay show">
    <div class="login-modal">
      <div class="modal-header">
        <div class="modal-logo">W</div>
        <div class="modal-title">Chào mừng bạn</div>
        <div class="modal-subtitle">Đăng nhập để tiếp tục đọc truyện</div>
      </div>
      <form action="login.php" method="POST" class="input-login">
        <div class="form-floating">
          <input type="text" name="login_name" id="login_name" placeholder=" " required autocomplete="username">
          <label for="login_name">Email hoặc tên đăng nhập</label>
        </div>
        <div class="form-floating">
          <input type="password" name="login_password" id="login_password" placeholder=" " required autocomplete="current-password">
          <label for="login_password">Mật khẩu</label>
        </div>
        <button type="submit">Đăng nhập</button>
      </form>
      <div class="divider"><span>hoặc</span></div>
      <div class="social-login">
        <div class="social-btn-circle">G</div>
        <div class="social-btn-circle">F</div>
      </div>
      <div class="footer-links">
        <a href="#" id="openRegister">Tạo tài khoản</a>
      </div>
    </div>
  </div>

  <!-- Modal đăng ký -->
  <div id="registerModal" class="modal-overlay" style="display:none;" onclick="closeRegisterModal(event)">
    <div class="login-modal" onclick="event.stopPropagation()">
      <div class="modal-header">
        <div class="modal-logo">W</div>
        <div class="modal-title">Tạo tài khoản</div>
        <div class="modal-subtitle">Gia nhập Webnovel ngay hôm nay</div>
      </div>
      <form class="input-login" method="POST" action="register.php">
        <div class="form-floating">
          <input type="text" name="name" id="register_name" placeholder=" " required autocomplete="username">
          <label for="register_name">Tên đăng nhập</label>
        </div>
        <div class="form-floating">
          <input type="email" name="email" id="register_email" placeholder=" " required autocomplete="email">
          <label for="register_email">Email</label>
        </div>
        <div class="form-floating">
          <input type="password" name="password" id="register_password" placeholder=" " required autocomplete="new-password">
          <label for="register_password">Mật khẩu</label>
        </div>
        <div class="form-floating">
          <input type="password" name="confirm_password" id="register_confirm" placeholder=" " required autocomplete="new-password">
          <label for="register_confirm">Nhập lại mật khẩu</label>
        </div>
        <button type="submit">ĐĂNG KÝ</button>
      </form>
      <div class="footer-links">
        <a href="#" id="backToLogin">Quay lại đăng nhập</a>
      </div>
    </div>
  </div>

  <script>
    // Modal chuyển đổi giữa login/register
    document.getElementById('openRegister').onclick = function(e) {
      e.preventDefault();
      document.getElementById('loginModal').style.display = 'none';
      document.getElementById('registerModal').style.display = 'flex';
    };
    document.getElementById('backToLogin').onclick = function(e) {
      e.preventDefault();
      document.getElementById('registerModal').style.display = 'none';
      document.getElementById('loginModal').style.display = 'flex';
    };
    // Đóng modal khi click nền (nếu cần)
    function closeRegisterModal(event) {
      if(event.target.id === 'registerModal') {
        document.getElementById('registerModal').style.display = 'none';
        document.getElementById('loginModal').style.display = 'flex';
      }
    }
  </script>
</body>
</html>