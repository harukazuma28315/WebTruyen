<?php
// Hàm partition dùng để phân tách mảng
function partition(&$arr, $low, $high) {
    $pivot = $arr[$high]; // Chọn phần tử cuối làm pivot
    $i = $low - 1; // Đặt chỉ số của phần tử nhỏ hơn pivot

    for ($j = $low; $j < $high; $j++) {
        if ($arr[$j] < $pivot) { // Nếu phần tử nhỏ hơn pivot
            $i++;
            // Hoán đổi arr[$i] và arr[$j]
            $temp = $arr[$i];
            $arr[$i] = $arr[$j];
            $arr[$j] = $temp;
        }
    }
    // Hoán đổi arr[$i+1] và arr[$high] (pivot)
    $temp = $arr[$i + 1];
    $arr[$i + 1] = $arr[$high];
    $arr[$high] = $temp;

    return $i + 1; // Trả về chỉ số của pivot
}

// Hàm QuickSort
function quickSort(&$arr, $low, $high) {
    if ($low < $high) {
        // Tìm chỉ số của phần tử phân tách
        $pi = partition($arr, $low, $high);
        
        // Đệ quy sắp xếp các phần bên trái và bên phải pivot
        quickSort($arr, $low, $pi - 1);
        quickSort($arr, $pi + 1, $high);
    }
}

// Ví dụ: Sắp xếp danh sách truyện theo tên (hoặc ngày tháng nếu thay đổi mảng)
// Dữ liệu ví dụ: Danh sách truyện
$novels = ["Truyện A", "Truyện C", "Truyện B", "Truyện D"];

// Gọi hàm QuickSort để sắp xếp
quickSort($novels, 0, count($novels) - 1);

// In ra kết quả
echo "Danh sách truyện sau khi sắp xếp:\n";
print_r($novels);
?>