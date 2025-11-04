<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: Registration/signin_new.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if user is a teacher
$stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if ($user['role'] !== 'teacher' && $user['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$assignment_id = intval($_GET['assignment_id'] ?? 0);

if ($assignment_id === 0) {
    header("Location: teacher_dashboard.php");
    exit();
}

// Get assignment details
$stmt = $conn->prepare("
    SELECT a.id, a.title, a.max_score, a.course_id, c.course_code, c.course_name, c.teacher_id
    FROM assignments a
    JOIN courses c ON a.course_id = c.id
    WHERE a.id = ?
");
$stmt->bind_param("i", $assignment_id);
$stmt->execute();
$assignment_result = $stmt->get_result();
$stmt->close();

if ($assignment_result->num_rows === 0) {
    header("Location: teacher_dashboard.php");
    exit();
}

$assignment = $assignment_result->fetch_assoc();

// Verify teacher owns this course
if ($assignment['teacher_id'] != $user_id && $user['role'] !== 'admin') {
    header("Location: teacher_dashboard.php");
    exit();
}

$error = '';
$success = '';

// Handle grade submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_grades'])) {
    $grades = $_POST['grades'] ?? [];
    $updated = 0;
    
    foreach ($grades as $student_id => $grade_data) {
        $student_id = intval($student_id);
        $score = floatval($grade_data['score'] ?? 0);
        $feedback = trim($grade_data['feedback'] ?? '');
        
        if ($score >= 0 && $score <= $assignment['max_score']) {
            $percentage = ($score / $assignment['max_score']) * 100;
            $letter_grade = '';
            if ($percentage >= 90) $letter_grade = 'A';
            elseif ($percentage >= 80) $letter_grade = 'B';
            elseif ($percentage >= 70) $letter_grade = 'C';
            elseif ($percentage >= 60) $letter_grade = 'D';
            else $letter_grade = 'F';
            
            // Check if grade exists
            $check_stmt = $conn->prepare("SELECT id FROM grades WHERE student_id = ? AND assignment_id = ?");
            $check_stmt->bind_param("ii", $student_id, $assignment_id);
            $check_stmt->execute();
            $exists = $check_stmt->get_result()->num_rows > 0;
            $check_stmt->close();
            
            if ($exists) {
                $update_stmt = $conn->prepare("UPDATE grades SET score = ?, max_score = ?, percentage = ?, letter_grade = ?, feedback = ? WHERE student_id = ? AND assignment_id = ?");
                $update_stmt->bind_param("dddssii", $score, $assignment['max_score'], $percentage, $letter_grade, $feedback, $student_id, $assignment_id);
                if ($update_stmt->execute()) $updated++;
                $update_stmt->close();
            } else {
                $insert_stmt = $conn->prepare("INSERT INTO grades (student_id, assignment_id, score, max_score, percentage, letter_grade, feedback) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $insert_stmt->bind_param("iidddss", $student_id, $assignment_id, $score, $assignment['max_score'], $percentage, $letter_grade, $feedback);
                if ($insert_stmt->execute()) $updated++;
                $insert_stmt->close();
            }
        }
    }
    
    if ($updated > 0) {
        $success = "Successfully saved $updated grade(s)!";
    } else {
        $error = "No valid grades to save.";
    }
}

// Get enrolled students with their grades
$stmt = $conn->prepare("
    SELECT u.id, u.name, u.email, g.score, g.feedback, g.graded_at
    FROM enrollments e
    JOIN users u ON e.student_id = u.id
    LEFT JOIN grades g ON g.student_id = u.id AND g.assignment_id = ?
    WHERE e.course_id = ?
    ORDER BY u.name ASC
");
$stmt->bind_param("ii", $assignment_id, $assignment['course_id']);
$stmt->execute();
$students = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter Grades - EduWell</title>
    <link rel="stylesheet" href="css/yawa.css">
    <style>
        .grade-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
        }
        .assignment-info {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .grade-form {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .grade-row {
            display: grid;
            grid-template-columns: 2fr 1fr 2fr;
            gap: 15px;
            padding: 15px;
            border-bottom: 1px solid #eee;
            align-items: center;
        }
        .grade-row:last-child {
            border-bottom: none;
        }
        .student-name {
            font-weight: bold;
            color: #333;
        }
        .grade-input {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 100%;
        }
        .grade-input:focus {
            outline: none;
            border-color: #007bff;
        }
        .max-score {
            color: #666;
            font-size: 14px;
        }
        .btn-submit {
            background: #007bff;
            color: white;
            padding: 15px 40px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
        }
        .btn-submit:hover {
            background: #0056b3;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
        }
        .form-header {
            display: grid;
            grid-template-columns: 2fr 1fr 2fr;
            gap: 15px;
            padding: 15px;
            background: #f8f9fa;
            font-weight: bold;
            border-bottom: 2px solid #007bff;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="grade-container">
        <div class="assignment-info">
            <h1><?= htmlspecialchars($assignment['title']) ?></h1>
            <p><strong>Course:</strong> <?= htmlspecialchars($assignment['course_code']) ?> - <?= htmlspecialchars($assignment['course_name']) ?></p>
            <p><strong>Maximum Score:</strong> <?= number_format($assignment['max_score'], 2) ?></p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <?php if ($students->num_rows === 0): ?>
            <div class="alert alert-error">
                No students enrolled in this course.
            </div>
        <?php else: ?>
            <form method="POST" action="" class="grade-form">
                <div class="form-header">
                    <div>Student</div>
                    <div>Score</div>
                    <div>Feedback</div>
                </div>
                
                <?php while ($student = $students->fetch_assoc()): ?>
                    <div class="grade-row">
                        <div class="student-name">
                            <?= htmlspecialchars($student['name']) ?><br>
                            <small style="color: #666;"><?= htmlspecialchars($student['email']) ?></small>
                            <?php if ($student['graded_at']): ?>
                                <br><small style="color: #999;">Graded: <?= date('M d, Y', strtotime($student['graded_at'])) ?></small>
                            <?php endif; ?>
                        </div>
                        <div>
                            <input type="number" 
                                   name="grades[<?= $student['id'] ?>][score]" 
                                   class="grade-input" 
                                   value="<?= $student['score'] ? number_format($student['score'], 2) : '' ?>" 
                                   min="0" 
                                   max="<?= $assignment['max_score'] ?>" 
                                   step="0.01"
                                   placeholder="0">
                            <div class="max-score">/ <?= number_format($assignment['max_score'], 2) ?></div>
                        </div>
                        <div>
                            <input type="text" 
                                   name="grades[<?= $student['id'] ?>][feedback]" 
                                   class="grade-input" 
                                   value="<?= htmlspecialchars($student['feedback'] ?? '') ?>" 
                                   placeholder="Optional feedback">
                        </div>
                    </div>
                <?php endwhile; ?>
                
                <button type="submit" name="save_grades" class="btn-submit">Save All Grades</button>
            </form>
        <?php endif; ?>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>

