<?php
// API endpoint for checking grade updates
session_start();
include 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$last_check = $_GET['last_check'] ?? null;

if ($last_check) {
    // Get only grades updated since last check
    $stmt = $conn->prepare("
        SELECT g.id, a.title, g.score, g.max_score, g.percentage, g.letter_grade, g.updated_at, c.course_code
        FROM grades g
        JOIN assignments a ON g.assignment_id = a.id
        JOIN courses c ON a.course_id = c.id
        WHERE g.student_id = ? AND g.updated_at > ?
        ORDER BY g.updated_at DESC
    ");
    $stmt->bind_param("is", $user_id, $last_check);
} else {
    // Get all recent grades
    $stmt = $conn->prepare("
        SELECT g.id, a.title, g.score, g.max_score, g.percentage, g.letter_grade, g.updated_at, c.course_code
        FROM grades g
        JOIN assignments a ON g.assignment_id = a.id
        JOIN courses c ON a.course_id = c.id
        WHERE g.student_id = ?
        ORDER BY g.updated_at DESC
        LIMIT 10
    ");
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$result = $stmt->get_result();

$updates = [];
while ($row = $result->fetch_assoc()) {
    $updates[] = $row;
}

echo json_encode([
    'success' => true, 
    'updates' => $updates,
    'count' => count($updates),
    'timestamp' => date('Y-m-d H:i:s')
]);
$stmt->close();

