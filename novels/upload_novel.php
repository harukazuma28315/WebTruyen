<?php
session_start();
include '../db_connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ../auth/login1_from.php');
    exit();
}
function sanitize_filename($str) {
    $str = iconv('UTF-8', 'ASCII//TRANSLIT', $str); // Bỏ dấu tiếng Việt
    $str = preg_replace('/[^a-zA-Z0-9]/', '_', $str); // Chỉ chữ & số
    return strtolower(trim($str, '_'));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id     = $_SESSION['user_id'];
    $tac_gia     = trim($_POST['tac_gia']);
    if ($tac_gia === "") {
    $tac_gia = $_SESSION['name'];
    }
    $tac_gia = mb_convert_case($tac_gia, MB_CASE_TITLE, "UTF-8"); // Viết hoa đầu từ
    $ten_truyen  = trim($_POST['ten_truyen']);
    $category_id = (int) $_POST['the_loai'];
    $tags        = $_POST['tags'] ?? [];
    $tom_tat     = htmlspecialchars(trim($_POST['tom_tat']));

    // 1. Xử lý ảnh bìa
    $cover_path = null;
    if (isset($_FILES['cover']) && $_FILES['cover']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($_FILES['cover']['type'], $allowed_types)) {
            $img_ext = strtolower(pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION));

            $safe_name = sanitize_filename($ten_truyen); // Đổi tên truyện thành tên file an toàn
            $new_name = $user_id . "_" . $safe_name ."." . $img_ext;

            $target_dir = "../images/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }

            $cover_path = $target_dir . $new_name;
            move_uploaded_file($_FILES['cover']['tmp_name'], $cover_path);
        }
    }


    // 2. Kiểm tra/Thêm tác giả
    $stmt = $conn->prepare("SELECT author_id FROM authors WHERE name = ?");
    $stmt->bind_param("s", $tac_gia);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($author_id);
        $stmt->fetch();
    } else {
        $stmt->close();
        $stmt = $conn->prepare("INSERT INTO authors (name) VALUES (?)");
        $stmt->bind_param("s", $tac_gia);
        $stmt->execute();
        $author_id = $stmt->insert_id;
    }
    $stmt->close();

    // 3. Thêm truyện
    $stmt = $conn->prepare("INSERT INTO novels (title, description, cover, category_id, created_by) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssii", $ten_truyen, $tom_tat, $cover_path, $category_id, $user_id);
    $stmt->execute();
    $novel_id = $stmt->insert_id;
    $stmt->close();

    // 4. Gán truyện với tác giả
    $stmt = $conn->prepare("INSERT INTO novel_author (novel_id, author_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $novel_id, $author_id);
    $stmt->execute();
    $stmt->close();

    // 5. Gán tags
    foreach ($tags as $tag_id) {
        $tag_id = (int)$tag_id;
        $stmt = $conn->prepare("INSERT INTO novel_tag (novel_id, tag_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $novel_id, $tag_id);
        $stmt->execute();
        $stmt->close();
    }

    // ✅ Hoàn tất
    header("Location: dashboard.php?success=1");
    exit();
} else {
    header("Location: create.php");
    exit();
}
?>
