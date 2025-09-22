<?php
session_start();
include '../db_connect.php'; // Kết nối đến cơ sở dữ liệu

// Lấy id truyện từ URL, mặc định = 1 nếu không có
$id = isset($_GET['id']) ? intval($_GET['id']) : 1;

// Lịch sử đọc
if (isset($_SESSION['user_id']) && isset($_GET['id'])) {
    $user_id = $_SESSION['user_id'];
    $novel_id = intval($_GET['id']);

    $sql_chap = "SELECT chapter_id FROM chapters WHERE novel_id = $novel_id ORDER BY number ASC LIMIT 1";
    $res_chap = mysqli_query($conn, $sql_chap);
    $row_chap = mysqli_fetch_assoc($res_chap);
    $chapter_id = $row_chap ? $row_chap['chapter_id'] : "NULL";

    $sql_history = "
        INSERT INTO reading_history (user_id, novel_id, chapter_id)
        VALUES ($user_id, $novel_id, $chapter_id)
        ON DUPLICATE KEY UPDATE
            chapter_id = VALUES(chapter_id),
            last_read_at = CURRENT_TIMESTAMP
    ";
    mysqli_query($conn, $sql_history);
}


// 1. Lấy thông tin truyện + thể loại chính
$sql_novel = "
    SELECT n.*, c.name AS category_name
    FROM novels n
    LEFT JOIN categories c ON n.category_id = c.category_id
    WHERE n.novel_id = ?
    LIMIT 1
";
$stmt = $conn->prepare($sql_novel);
$stmt->bind_param("i", $id);
$stmt->execute();
$novel = $stmt->get_result()->fetch_assoc();
$stmt->close();
// Kiểm tra truyện đã có trong thư viện chưa
$is_in_library = false;
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $sql_check_library = "SELECT 1 FROM user_library WHERE user_id = $uid AND novel_id = $id";
    $res_check = mysqli_query($conn, $sql_check_library);
    if (mysqli_num_rows($res_check) > 0) {
        $is_in_library = true;
    }
}


if (!$novel) {
    echo "❌ Không tìm thấy truyện.";
    exit;
}

$category_id = $novel['category_id'];

// 2. Tác giả
$sql_authors = "
    SELECT a.author_id, a.name
    FROM authors a
    JOIN novel_author na ON a.author_id = na.author_id
    WHERE na.novel_id = ?
";
$stmt = $conn->prepare($sql_authors);
$stmt->bind_param("i", $id);
$stmt->execute();
$result_authors = $stmt->get_result();
$authors = [];
while ($row = $result_authors->fetch_assoc()) {
    $authors[] = [
        'id' => $row['author_id'],
        'name' => $row['name']
    ];
}
$stmt->close();

// 3. Danh sách chương
$sql_chaps = "
    SELECT chapter_id, number, title
    FROM chapters
    WHERE novel_id = ?
    ORDER BY number ASC
";
$stmt = $conn->prepare($sql_chaps);
$stmt->bind_param("i", $id);
$stmt->execute();
$result_chaps = $stmt->get_result();
$chapters = [];
while ($row = $result_chaps->fetch_assoc()) {
    $chapters[$row['number']] = [
        'id' => $row['chapter_id'],
        'title' => $row['title'],
        'number' => $row['number']
    ];
}
$stmt->close();

// 4. Thẻ tag phụ
$sql_tags = "
    SELECT t.tag_id, t.name
    FROM tags t
    JOIN novel_tag nt ON t.tag_id = nt.tag_id
    WHERE nt.novel_id = ?
";
$stmt = $conn->prepare($sql_tags);
$stmt->bind_param("i", $id);
$stmt->execute();
$result_tags = $stmt->get_result();
$tag_ids = [];
while ($row = $result_tags->fetch_assoc()) {
    $tag_ids[$row['tag_id']] = $row['name'];
}
$stmt->close();

$chap_dau = !empty($chapters) ? min(array_keys($chapters)) : null;
$chap_moi = !empty($chapters) ? max(array_keys($chapters)) : null;
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($novel['title']); ?></title>
    <link rel="stylesheet" href="../css/thongtin.css?v=<?php echo time(); ?>">
        <link rel="stylesheet" href="../css/dangnhap.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
    a.author-link {
        color: #333;
        text-decoration: none;
        font-weight: 500;
        margin-right: 10px;
    }
    a.author-link:hover {
        color: #007bff;
        text-decoration: underline;
    }
    #imglogo {
	height: 60px;
	width: auto;
    margin: -20px 20px 0px 20px;
    padding: 0px 20px 0px 0px;
    }

    </style>
</head>
<body>
    <div class="gggg">
        <a href="../index.php"><img src="../image/logoko.png" alt="logo" id="imglogo"></a>
        <a href="../index.php"><i class="fa fa-home"></i></a> /
        <a href="theloai.php?id=<?php echo urlencode($novel['category_id']); ?>&type=category">
            <?php echo htmlspecialchars($novel['category_name']); ?>
        </a> / 
        <span class="ggggtentruyen"><?php echo htmlspecialchars($novel['title']); ?></span>
    </div>

    <div class="wrapper">
        <div class="khungtruyen">
            <div>
                <img src="<?php echo htmlspecialchars($novel['cover']); ?>" width="240" alt="cover">
            </div>
            <div class="thongtin">
                <h1><?php echo htmlspecialchars($novel['title']); ?></h1>
                <div class="theloai">
                    <a href="theloai.php?id=<?php echo urlencode($novel['category_id']); ?>&type=category">
                        <?php echo htmlspecialchars($novel['category_name']); ?>
                    </a>
                    <?php foreach ($tag_ids as $tag_id => $tag_name): ?>
                        , <a href="theloai.php?id=<?php echo $tag_id; ?>&type=tag"><?php echo htmlspecialchars($tag_name); ?></a>
                    <?php endforeach; ?>
                </div>

                <p><strong>Tác giả: <?php foreach ($authors as $tacgia): ?>
                    <?php echo htmlspecialchars($tacgia['name']); ?></strong>
                <?php endforeach; ?> </p>
                

                <div class="nut-chap">
                    <?php if ($chap_dau): ?>
                        <?php
                        $chap_dau_href = ($category_id == 2)
                            ? "chapimages.php?truyen={$id}&chap={$chap_dau}"
                            : "chap.php?truyen={$id}&chap={$chapters[$chap_dau]['number']}";

                        $chap_moi_href = ($category_id == 2)
                            ? "chapimages.php?truyen={$id}&chap={$chap_moi}"
                            : "chap.php?truyen={$id}&chap={$chapters[$chap_moi]['number']}";
                        ?>
                        <a class="nut" href="<?php echo $chap_dau_href; ?>">ĐỌC TỪ ĐẦU</a>
                        <a class="nut" href="<?php echo $chap_moi_href; ?>">ĐỌC CHAP MỚI NHẤT</a>
                    <?php else: ?>
                        <span>Chưa có chương nào</span>
                    <?php endif; ?>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <button id="libraryBtn" class="nut" onclick="thaydoiLB(this)" data-novel-id="<?= $id ?>">
                                    <?= $is_in_library ? "ĐÃ THÊM" : "THÊM VÀO THƯ VIỆN" ?>
                                </button>
                            <?php else: ?>
                                <a class="nut" href="../index/index.php?login=1">ĐĂNG NHẬP ĐỂ LƯU TRUYỆN</a>
                            <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="tomtat">
            <h2>📖 Tóm tắt</h2>
            <p><?php echo nl2br(htmlspecialchars($novel['description'])); ?></p>
        </div>

        <h2>Mục Lục:</h2>
        <?php if (!empty($chapters)): ?>
            <div class="chap">
                <div class="cot">
                    <?php foreach ($chapters as $num => $chap): ?>
                        <?php if ($num % 2 != 0): ?>
                            <?php
                            $href = ($category_id == 2)
                                ? "chapimages.php?truyen={$id}&chap={$chap['number']}"
                                : "chap.php?truyen={$id}&chap={$chap['number']}";
                            ?>
                            <button class="item-chap" onclick="location.href='<?php echo $href; ?>'">
                                Chương <?php echo $num; ?>: <?php echo htmlspecialchars($chap['title']); ?>
                            </button>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <div class="cot">
                    <?php foreach ($chapters as $num => $chap): ?>
                        <?php if ($num % 2 == 0): ?>
                            <?php
                            $href = ($category_id == 2)
                                ? "chapimages.php?truyen={$id}&chap={$chap['number']}"
                                : "chap.php?truyen={$id}&chap={$chap['number']}";
                            ?>
                            <button class="item-chap" onclick="location.href='<?php echo $href; ?>'">
                                Chương <?php echo $num; ?>: <?php echo htmlspecialchars($chap['title']); ?>
                            </button>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <p>Chưa có chương nào.</p>
        <?php endif; ?>
    </div>



   
    <script type="text/javascript" src="../js/addlibrary.js" ></script> 
</body>
</html>
<?php $conn->close(); ?>
