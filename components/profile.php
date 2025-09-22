<?php
session_start();
include '../db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index/index.php?login=1');
    exit();
}

$user_id = $_SESSION['user_id'];

// Lấy thông tin user
$stmt = $conn->prepare("SELECT name, email, avatar, level, created_at FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$avatar = !empty($user['avatar']) ? $user['avatar'] : 'default-avatar.png';

// Đếm số bình luận
$stmt = $conn->prepare("SELECT COUNT(*) AS total_comments FROM comments WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$total_comments = $result->fetch_assoc()['total_comments'];

// Đếm số truyện đã tạo
$stmt = $conn->prepare("SELECT COUNT(*) AS total_novels FROM novels WHERE created_by = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$total_novels = $result->fetch_assoc()['total_novels'];

// Lấy danh sách truyện đã tạo
$stmt = $conn->prepare("SELECT novel_id, title, approval, created_at FROM novels WHERE created_by = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$novels = [];
while ($row = $result->fetch_assoc()) {
    $novels[] = $row;
}

// Lấy danh sách truyện do người dùng đăng
$stmt = $conn->prepare("
    SELECT 
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
    ORDER BY n.created_at DESC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hồ sơ cá nhân</title>
    <link rel="stylesheet" href="../css/profile.css">
</head>
<body>
    <div class="container">
        <h2>👤 Hồ sơ của bạn</h2>
        <div class="header">
            <img src="../image/avatars/<?php echo htmlspecialchars($avatar); ?>" alt="Avatar" class="avatar">
            <div class="info">
                <p><strong>Tên:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Tham gia:</strong> <?php echo date('d/m/Y', strtotime($user['created_at'])); ?></p>
                <p><strong>Cấp bậc:</strong> <?php echo $user['level'] == 1 ? 'Admin' : 'Thành viên'; ?></p>
                <?php if ($user['level'] == 1): ?>
                    <a href="../admin/admin_novels.php" class="admin-link">🔧 Quản trị</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="stats">
            <!-- <p>🗨️ Bình luận đã viết: <strong><?php echo $total_comments; ?></strong></p> -->
            <p>✍️ Truyện đã đăng: <strong><?php echo $total_novels; ?></strong></p>
        </div>

        <div class="actions">
            <!-- Nút hiện form đổi mật khẩu -->
            <button id="togglePasswordForm" style="margin-top: 20px;">Đổi mật khẩu</button>

            <!-- Form đổi mật khẩu -->
            <div id="passwordForm" style="display: none; margin-top: 15px;">
            <form method="post" action="change_password.php">
                <label for="current_password">Mật khẩu hiện tại:</label><br>
                <input type="password" name="current_password" id="current_password" required><br><br>

                <label for="new_password">Mật khẩu mới:</label><br>
                <input type="password" name="new_password" id="new_password" required><br><br>

                <label for="confirm_password">Xác nhận mật khẩu mới:</label><br>
                <input type="password" name="confirm_password" id="confirm_password" required><br><br>

                <button type="submit">Xác nhận</button>
            </form>
            </div>
            <a href="../auth/logout.php">Đăng xuất</a>
            <a href="../index/index.php">Home</a>
        </div>
        <div>
          <form action="update_avatar.php" method="post" enctype="multipart/form-data">
            <label for="avatar" class="custom-file-label">📁 Chọn ảnh đại diện mới</label>
            <input type="file" id="avatar" name="avatar" accept="image/*" required hidden>
            <button type="submit">Cập nhật avatar</button>
          </form>
          
        </div>
        <div class="novel-list">
        <p class="section-title"> Dưới đây là các truyện bạn đã đăng:</p>

        <a href="../novels/create.php" class="create-btn">➕ Tạo truyện mới</a>

        <hr><br>

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
        </div>
    </div>
    <script type="text/javascript" src="../js/imgavatar.js"></script>
    <script>
        document.getElementById("togglePasswordForm").addEventListener("click", function() {
        const form = document.getElementById("passwordForm");
        form.style.display = form.style.display === "none" ? "block" : "none";
        });
    </script>
</body>
</html>
