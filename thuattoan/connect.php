<?php
// Include kết nối cơ sở dữ liệu và các file sắp xếp
include_once '../novels/thongtin.php';  // Kết nối cơ sở dữ liệu
include_once 'Quicksort.php';  // Thuật toán Quick Sort
include_once 'ssday.php';  // Thuật toán sắp xếp theo ngày

// Hàm để lấy và sắp xếp truyện từ cơ sở dữ liệu
function getSortedNovels() {
    global $conn;  // Sử dụng kết nối cơ sở dữ liệu toàn cục

    // Lấy dữ liệu từ cơ sở dữ liệu
    $sql = "SELECT * FROM novels";
    $result = mysqli_query($conn, $sql);

    $novels = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $novels[] = $row;
    }

// Nếu bạn muốn sắp xếp theo tên, sử dụng QuickSort:
quickSort($novels, 0, count($novels) - 1);

// Nếu bạn muốn sắp xếp theo ngày, sử dụng hàm compareByDate:
usort($novels, 'compareByDate');

// Đóng kết nối
mysqli_close($conn);
    return $novels;
}

?>
<?php
// index.php

// Bao gồm file xử lý và sắp xếp dữ liệu
include_once '../thuattoan/connect.php';  // Bao gồm file xử lý sắp xếp và kết nối CSDL

// Lấy dữ liệu đã sắp xếp từ hàm trong fetch_and_sort_from_db.php
$novels = getSortedNovels();  // Giả sử hàm này trả về danh sách truyện đã sắp xếp

// Hiển thị kết quả
foreach ($novels as $novel) {
    echo "Tên truyện: " . htmlspecialchars($novel['title'], ENT_QUOTES, 'UTF-8') . "<br>";
    echo "Ngày tạo: " . $novel['created_at'] . "<br><br>";
}
?>