<?php
// API endpoint for getting grades (real-time updates)
session_start();
include 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$course_id = $_GET['course_id'] ?? null;

// Get student's grades
$query = "
    SELECT a.id, a.title, a.max_score, g.score, g.percentage, g.letter_grade, g.graded_at, g.updated_at
    FROM enrollments e
    JOIN assignments a ON a.course_id = e.course_id
    LEFT JOIN grades g ON g.assignment_id = a.id AND g.student_id = ?
    WHERE e.student_id = ?
";

if ($course_id) {
    $query .= " AND a.course_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $user_id, $user_id, $course_id);
} else {
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $user_id);
}

$stmt->execute();
$result = $stmt->get_result();

$grades = [];
while ($row = $result->fetch_assoc()) {
    $grades[] = $row;
}

echo json_encode(['success' => true, 'grades' => $grades]);
$stmt->close();

