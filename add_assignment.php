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

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = intval($_POST['course_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $max_score = floatval($_POST['max_score'] ?? 100);
    $due_date = $_POST['due_date'] ?? null;
    $assignment_type = trim($_POST['assignment_type'] ?? 'Assignment');
    
    // Verify course belongs to teacher
    $verify_stmt = $conn->prepare("SELECT id FROM courses WHERE id = ? AND teacher_id = ?");
    $verify_stmt->bind_param("ii", $course_id, $user_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    
    if ($verify_result->num_rows === 0) {
        $error = "Invalid course selected.";
    } elseif (empty($title)) {
        $error = "Assignment title is required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO assignments (course_id, title, description, max_score, due_date, assignment_type) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issdss", $course_id, $title, $description, $max_score, $due_date, $assignment_type);
        
        if ($stmt->execute()) {
            $success = "Assignment created successfully!";
            $_POST = [];
        } else {
            $error = "Failed to create assignment. Please try again.";
        }
        $stmt->close();
    }
    $verify_stmt->close();
}

// Get teacher's courses
$stmt = $conn->prepare("SELECT id, course_code, course_name FROM courses WHERE teacher_id = ? ORDER BY course_code");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$courses = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Assignment - EduWell</title>
    <link rel="stylesheet" href="css/yawa.css">
    <style>
        .form-container {
            max-width: 600px;
            margin: 40px auto;
            padding: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            font-family: inherit;
        }
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #007bff;
        }
        .btn-submit {
            background: #007bff;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
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
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="form-container">
        <h1>Add Assignment</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <?php if ($courses->num_rows === 0): ?>
            <div class="alert alert-error">
                You need to create a course first. <a href="create_course.php">Create Course</a>
            </div>
        <?php else: ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="course_id">Course *</label>
                    <select id="course_id" name="course_id" required>
                        <option value="">Select a Course</option>
                        <?php while ($course = $courses->fetch_assoc()): ?>
                            <option value="<?= $course['id'] ?>" 
                                    <?= (($_POST['course_id'] ?? '') == $course['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($course['course_code']) ?> - <?= htmlspecialchars($course['course_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="title">Assignment Title *</label>
                    <input type="text" id="title" name="title" 
                           value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" 
                           placeholder="e.g., Midterm Exam" required>
                </div>
                
                <div class="form-group">
                    <label for="assignment_type">Assignment Type</label>
                    <select id="assignment_type" name="assignment_type">
                        <option value="Assignment" <?= (($_POST['assignment_type'] ?? 'Assignment') === 'Assignment') ? 'selected' : '' ?>>Assignment</option>
                        <option value="Quiz" <?= (($_POST['assignment_type'] ?? '') === 'Quiz') ? 'selected' : '' ?>>Quiz</option>
                        <option value="Exam" <?= (($_POST['assignment_type'] ?? '') === 'Exam') ? 'selected' : '' ?>>Exam</option>
                        <option value="Project" <?= (($_POST['assignment_type'] ?? '') === 'Project') ? 'selected' : '' ?>>Project</option>
                        <option value="Homework" <?= (($_POST['assignment_type'] ?? '') === 'Homework') ? 'selected' : '' ?>>Homework</option>
                        <option value="Lab" <?= (($_POST['assignment_type'] ?? '') === 'Lab') ? 'selected' : '' ?>>Lab</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" 
                              placeholder="Assignment description and instructions..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="max_score">Maximum Score</label>
                    <input type="number" id="max_score" name="max_score" 
                           value="<?= htmlspecialchars($_POST['max_score'] ?? 100) ?>" 
                           min="1" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label for="due_date">Due Date</label>
                    <input type="date" id="due_date" name="due_date" 
                           value="<?= htmlspecialchars($_POST['due_date'] ?? '') ?>">
                </div>
                
                <button type="submit" class="btn-submit">Create Assignment</button>
            </form>
        <?php endif; ?>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>

