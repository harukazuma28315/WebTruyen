<?php
// Hàm so sánh ngày tháng
function compareByDate($a, $b) {
    return strtotime($a['created_at']) - strtotime($b['created_at']);
}

// Sắp xếp mảng truyện theo ngày tạo
usort($novels, 'compareByDate');

// Hiển thị kết quả
print_r($novels);
?>
