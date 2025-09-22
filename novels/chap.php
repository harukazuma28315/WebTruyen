<?php
session_start();
include '../db_connect.php'; // Kết nối đến cơ sở dữ liệu

// Lấy id truyện và số chương từ URL
$novel_id = isset($_GET['truyen']) ? intval($_GET['truyen']) : 0;
$chap_num = isset($_GET['chap']) ? intval($_GET['chap']) : 0;

// Kiểm tra hợp lệ
if (!$novel_id || !$chap_num) {
    die("❌ Dữ liệu không hợp lệ.");
}

// Lấy thông tin truyện
$stmt = $conn->prepare("SELECT * FROM novels WHERE novel_id = ?");
$stmt->bind_param("i", $novel_id);
$stmt->execute();
$truyen = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$truyen) {
    die("❌ Truyện không tồn tại.");
}

// Lấy thông tin chương
$stmt = $conn->prepare("SELECT * FROM chapters WHERE novel_id = ? AND number = ?");
$stmt->bind_param("ii", $novel_id, $chap_num);
$stmt->execute();
$chapter = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$chapter) {
    die("❌ Chương không tồn tại.");
}

// Lấy số chương tối đa/min cho chuyển trang
$stmt = $conn->prepare("SELECT MAX(number) as max_num, MIN(number) as min_num FROM chapters WHERE novel_id = ?");
$stmt->bind_param("i", $novel_id);
$stmt->execute();
$res_max = $stmt->get_result()->fetch_assoc();
$stmt->close();

$chap_prev = $chap_num - 1;
$chap_next = $chap_num + 1;
$min_num = $res_max['min_num'];
$max_num = $res_max['max_num'];

// Nếu người dùng đã đăng nhập, thì lưu lịch sử đọc chương
if (isset($_SESSION['user_id'])) {
    $user_id = intval($_SESSION['user_id']);

    // Lấy chapter_id tương ứng với novel_id và number (đã có ở trên)
    $stmt = $conn->prepare("SELECT chapter_id FROM chapters WHERE novel_id = ? AND number = ?");
    $stmt->bind_param("ii", $novel_id, $chap_num);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();

    if ($row) {
        $chapter_id = $row['chapter_id'];

        // Chèn hoặc cập nhật lịch sử đọc
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
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($truyen['title']) . " - Chương " . $chapter['number']; ?></title>
    <link rel="stylesheet" href="../css/chap.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<a id="top"></a>
<div class="chapter-nav">
    <a class="nav-btn home" href="../index.php" title="Trang chủ"><i class="fa-solid fa-house"></i></a>
    <a class="nav-btn list" href="../novels/thongtin.php?id=<?php echo $novel_id; ?>" title="Thông tin truyện"><i class="fa-solid fa-bars"></i></a>
    <a class="nav-btn prev" 
        href="<?php echo ($chap_prev >= $min_num) ? "../novels/chap.php?truyen=$novel_id&chap=$chap_prev" : 'javascript:void(0)'; ?>"
        <?php if ($chap_prev < $min_num) echo 'onclick="alert(\'Đã hết chương!\')"'; ?>>
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <span class="chapter-number">Chương <?php echo $chapter['number']; ?></span>
    <a class="nav-btn next" 
        href="<?php echo ($chap_next <= $max_num) ? "../novels/chap.php?truyen=$novel_id&chap=$chap_next" : 'javascript:void(0)'; ?>"
        <?php if ($chap_next > $max_num) echo 'onclick="alert(\'Đã hết chương!\')"'; ?>>
        <i class="fa-solid fa-arrow-right"></i>
    </a>
</div>
<a href="#top" class="back-to-top"><i class="fa-solid fa-arrow-up"></i></a>

<div class="chapter-content-wrapper">
    <h1><?php echo htmlspecialchars($truyen['title']); ?></h1>
    <h2><?php echo htmlspecialchars($chapter['title'] ?: "Chương " . $chapter['number']); ?></h2>
    <div class="chapter-content">
        <?php 
            $content = trim($chapter['content']);
            echo $content 
                ? nl2br(htmlspecialchars($content)) 
                : "<i>Chưa có nội dung cho chương này.</i>";
        ?>
    </div>
</div>
</body>
</html>
<?php $conn->close(); ?>
