<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập & Đăng ký - Webnovel</title>
    <link rel="stylesheet" href="dangnhap.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        .center-form {
            margin: 60px auto;
            max-width: 420px;
        }
        .login-modal {
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        }
    </style>
    <link rel="stylesheet" href="../css/dangnhap1.css">
</head>
<body>
    <div class="center-form">
        <!-- Form Đăng Nhập -->
        <div class="login-modal" id="formLogin">
            <div class="modal-header">
                <div class="modal-logo">W</div>
                <h2 class="modal-title">Chào mừng bạn đến với Webnovel</h2>
                <p class="modal-subtitle">Truy cập vào rất nhiều tiểu thuyết và truyện tranh chỉ bằng một cú nhấp</p>
            </div>
            <form class="input-login" method="POST" action="login1.php">
                <input type="text" name="login_name" placeholder="Tên đăng nhập" required>
                <input type="password" name="login_password" placeholder="Mật khẩu" required>
                <button type="submit">ĐĂNG NHẬP</button>
            </form>
            <div class="divider"><span>HOẶC</span></div>
            <div class="social-login">
                <div class="social-btn-circle" title="Google">
                    <img src="../image/google.png" alt="Google" style="width:24px;height:24px;">
                </div>
                <div class="social-btn-circle" title="Facebook">
                    <img src="../image/facebook.png" alt="Facebook" style="width:24px;height:24px;">
                </div>
            </div>
            <div style="text-align:center; margin-top:20px;">
                <a href="#" id="showRegister" style="color:#667eea; text-decoration:none; font-weight:500;">TẠO TÀI KHOẢN</a>
            </div>
            <div class="footer-links">
                © 2025 Webnovel | <a href="#">Điều khoản dịch vụ</a> | <a href="#">Chính sách bảo mật</a>
            </div>
        </div>
        <!-- Form Đăng Ký -->
        <div class="login-modal" id="formRegister" style="display:none;">
            <div class="modal-header">
                <div class="modal-logo">W</div>
                <h2 class="modal-title">Đăng ký tài khoản</h2>
                <p class="modal-subtitle">Tham gia Webnovel và khám phá kho truyện hấp dẫn!</p>
            </div>
            <form class="input-login" method="POST" action="register.php">
                <input type="text" name="name" placeholder="Tên đăng nhập" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Mật khẩu" required>
                <input type="password" name="confirm_password" placeholder="Xác nhận mật khẩu" required>
                <button type="submit">ĐĂNG KÝ</button>
            </form>
            <div style="text-align:center; margin-top:20px;">
                <a href="#" id="showLogin" style="color:#667eea; text-decoration:none; font-weight:500;">Đã có tài khoản? Đăng nhập</a>
            </div>
        </div>
    </div>
    <script>
        // Alert social login
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll(".social-btn-circle").forEach((btn) => {
                btn.addEventListener("click", function () {
                    alert("Tùy chọn đăng nhập mạng xã hội!");
                });
            });
            // Chuyển form
            document.getElementById("showRegister").onclick = function(e){
                e.preventDefault();
                document.getElementById("formLogin").style.display="none";
                document.getElementById("formRegister").style.display="block";
            }
            document.getElementById("showLogin").onclick = function(e){
                e.preventDefault();
                document.getElementById("formLogin").style.display="block";
                document.getElementById("formRegister").style.display="none";
            }
        });
    </script>
</body>
</html>
