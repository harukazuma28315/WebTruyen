<?php
include '../db_connect.php'; // Kết nối CSDL

$q = $_GET['q'] ?? '';
$q = trim($q);

if ($q === '') {
    header('Content-Type: application/json');
    echo json_encode([]);
    exit;
}

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
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["error" => "Query prepare failed"]);
    exit;
}

$searchTerm = "%" . $q . "%"; // dùng cho WHERE + cuối ORDER BY
$startTerm = $q;              // dùng cho CONCAT(?, '%')

$stmt->bind_param("sss", $searchTerm, $startTerm, $searchTerm);

$stmt->execute();
$result = $stmt->get_result();

$data = $result->fetch_all(MYSQLI_ASSOC);

header('Content-Type: application/json; charset=utf-8');
echo json_encode($data, JSON_UNESCAPED_UNICODE);

$stmt->close();
$conn->close();
?>

