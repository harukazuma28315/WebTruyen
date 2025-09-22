<?php
include '../db_connect.php'; // Kết nối đến cơ sở dữ liệu

$q = $_GET['q'] ?? '';
if (empty($q)) {
    echo json_encode([]);
    exit;
}

// Câu lệnh SQL chỉ tìm trong title
$sql = "
SELECT novel_id, title, description, cover
FROM novels
WHERE title LIKE ?
ORDER BY 
  CASE 
    WHEN LOWER(title) LIKE LOWER(CONCAT(?, '%')) THEN 0
    WHEN LOWER(title) LIKE LOWER(?) THEN 1
    ELSE 2
  END,
  title ASC
LIMIT 30
";

$stmt = $conn->prepare($sql);
$searchTerm = "%" . $q . "%";
$startTerm = $q;
$stmt->bind_param("sss", $searchTerm, $startTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);
?>
