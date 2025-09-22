<?php
include '../db_connect.php'; // Kết nối đến cơ sở dữ liệu

$novel_id = isset($_GET['truyen']) ? intval($_GET['truyen']) : 0;
$chapter_number = isset($_GET['chap']) ? intval($_GET['chap']) : 0;

if (!$novel_id || !$chapter_number) {
    die("❌ Thiếu dữ liệu");
}


// Lấy chương tương ứng
$sql = "SELECT chapter_id, novel_id, number FROM chapters WHERE novel_id = ? AND number = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $novel_id, $chapter_number);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Không tìm thấy chương");
}

$chapter = $result->fetch_assoc();
$chapter_id = $chapter['chapter_id'];
// Lưu lịch sử đọc nếu đã đăng nhập
session_start();
if (isset($_SESSION['user_id'])) {
    $user_id = intval($_SESSION['user_id']);

    $stmt = $conn->prepare("
        INSERT INTO reading_history (user_id, novel_id, chapter_id, last_read_at)
        VALUES (?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE 
            chapter_id = VALUES(chapter_id),
            last_read_at = NOW()
    ");
    $stmt->bind_param("iii", $user_id, $novel_id, $chapter_id);
    $stmt->execute();
    $stmt->close();
}



// Tìm chương trước
$sql_prev = "SELECT chapter_id FROM chapters WHERE novel_id = ? AND chapter_id < ? ORDER BY chapter_id DESC LIMIT 1";
$stmt_prev = $conn->prepare($sql_prev);
$stmt_prev->bind_param("ii", $novel_id, $chapter_id);
$stmt_prev->execute();
$result_prev = $stmt_prev->get_result();
$chap_prev_id = ($result_prev->num_rows > 0) ? $result_prev->fetch_assoc()['chapter_id'] : null;

// Tìm chương sau
$sql_next = "SELECT chapter_id FROM chapters WHERE novel_id = ? AND chapter_id > ? ORDER BY chapter_id ASC LIMIT 1";
$stmt_next = $conn->prepare($sql_next);
$stmt_next->bind_param("ii", $novel_id, $chapter_id);
$stmt_next->execute();
$result_next = $stmt_next->get_result();
$chap_next_id = ($result_next->num_rows > 0) ? $result_next->fetch_assoc()['chapter_id'] : null;

// Lấy ảnh
$sql_img = "SELECT image_url FROM chapter_images WHERE chapter_id = ? ORDER BY image_order ASC";
$stmt_img = $conn->prepare($sql_img);
$stmt_img->bind_param("i", $chapter_id);
$stmt_img->execute();
$result_img = $stmt_img->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Đọc truyện tranh</title>
    <link href="https://cdn.jsdelivr.net/npm/lightbox2@2/dist/css/lightbox.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <style>
        body {
            font-family: Arial;
            background: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 700px;
            margin: auto;
            padding: 30px;
            background: white;
        }
        .image-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 80px;
        }

        .image-box {
            width: 800px;
            display: flex;
            justify-content: center;
        }

        .image-box img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        .chapter-nav {
            position: fixed;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            background: #ffffffee;
            border: 1px solid #ccc;
            border-radius: 10px;
            padding: 8px 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .nav-btn {
            background: #007bff;
            color: white;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 16px;
            transition: background 0.2s;
        }

        .nav-btn:hover {
            background: #0056b3;
        }
        .chapter-number {
            font-weight: bold;
            margin: 0 15px;
            font-size: 18px;
            color: #333;
        }
        .back-to-top {
            position: fixed;
            bottom: 25px;
            right: 25px;
            background: #007bff;
            color: white;
            padding: 10px 13px;
            border-radius: 50%;
            text-decoration: none;
            font-size: 18px;
        }
        .back-to-top:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>

<a name="top"></a>
<div class="container">

    <!-- Điều hướng chương -->
    <div class="chapter-nav">
        <a class="nav-btn home" href="../index.php" title="Trang chủ"><i class="fa-solid fa-house"></i></a>

        <a class="nav-btn list" href="thongtin.php?id=<?php echo $novel_id; ?>" title="Thông tin truyện">
            <i class="fa-solid fa-bars"></i>
        </a>

        <a class="nav-btn prev"
        href="chapimages.php?truyen=<?php echo $novel_id; ?>&chap=<?php echo $chapter['number'] - 1; ?>"
        <?php if (!$chap_prev_id) echo 'onclick="alert(\'Đã hết chương!\')"'; ?>>
        <i class="fa-solid fa-arrow-left"></i>
        </a>


        <span class="chapter-number">Chương <?php echo $chapter['number']; ?></span>

        <a class="nav-btn next"
        href="chapimages.php?truyen=<?php echo $novel_id; ?>&chap=<?php echo $chapter['number'] + 1; ?>"
        <?php if (!$chap_next_id) echo 'onclick="alert(\'Đã hết chương!\')"'; ?>>
        <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>

    <!-- Hiển thị ảnh truyện -->
    <div class="image-wrapper">
        <?php
        if ($result_img->num_rows === 0) {
            echo "<p>Không có ảnh nào cho chương này.</p>";
        } else {
            while ($row = $result_img->fetch_assoc()) {
                $img = htmlspecialchars($row['image_url']);
                echo "
                <div class='image-box'>
                    <img src='../$img' alt='Trang truyện'>
                </div>";
            }
        }
        ?>
    </div>
</div>
<!-- Nút lên đầu -->
<a href="#top" class="back-to-top" title="Lên đầu trang"><i class="fa-solid fa-arrow-up"></i></a>
</body>
</html>
