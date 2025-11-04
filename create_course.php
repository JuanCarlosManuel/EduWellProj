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
    $course_code = trim($_POST['course_code'] ?? '');
    $course_name = trim($_POST['course_name'] ?? '');
    $semester = trim($_POST['semester'] ?? '');
    $academic_year = trim($_POST['academic_year'] ?? '');
    
    if (empty($course_code) || empty($course_name)) {
        $error = "Course code and name are required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO courses (course_code, course_name, teacher_id, semester, academic_year) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiss", $course_code, $course_name, $user_id, $semester, $academic_year);
        
        if ($stmt->execute()) {
            $success = "Course created successfully!";
            // Clear form
            $_POST = [];
        } else {
            $error = "Failed to create course. Please try again.";
        }
        $stmt->close();
    }
}

// Get teacher's courses for reference
$stmt = $conn->prepare("SELECT course_code, course_name FROM courses WHERE teacher_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent_courses = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Course - EduWell</title>
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
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .form-group input:focus,
        .form-group select:focus {
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
        <h1>Create New Course</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="course_code">Course Code *</label>
                <input type="text" id="course_code" name="course_code" 
                       value="<?= htmlspecialchars($_POST['course_code'] ?? '') ?>" 
                       placeholder="e.g., CS101" required>
            </div>
            
            <div class="form-group">
                <label for="course_name">Course Name *</label>
                <input type="text" id="course_name" name="course_name" 
                       value="<?= htmlspecialchars($_POST['course_name'] ?? '') ?>" 
                       placeholder="e.g., Introduction to Computer Science" required>
            </div>
            
            <div class="form-group">
                <label for="semester">Semester</label>
                <select id="semester" name="semester">
                    <option value="">Select Semester</option>
                    <option value="Fall" <?= (($_POST['semester'] ?? '') === 'Fall') ? 'selected' : '' ?>>Fall</option>
                    <option value="Spring" <?= (($_POST['semester'] ?? '') === 'Spring') ? 'selected' : '' ?>>Spring</option>
                    <option value="Summer" <?= (($_POST['semester'] ?? '') === 'Summer') ? 'selected' : '' ?>>Summer</option>
                    <option value="Winter" <?= (($_POST['semester'] ?? '') === 'Winter') ? 'selected' : '' ?>>Winter</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="academic_year">Academic Year</label>
                <input type="text" id="academic_year" name="academic_year" 
                       value="<?= htmlspecialchars($_POST['academic_year'] ?? '') ?>" 
                       placeholder="e.g., 2024-2025">
            </div>
            
            <button type="submit" class="btn-submit">Create Course</button>
        </form>
        
        <?php if ($recent_courses->num_rows > 0): ?>
            <div style="margin-top: 40px;">
                <h3>Recent Courses</h3>
                <ul>
                    <?php while ($course = $recent_courses->fetch_assoc()): ?>
                        <li><?= htmlspecialchars($course['course_code']) ?> - <?= htmlspecialchars($course['course_name']) ?></li>
                    <?php endwhile; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>

