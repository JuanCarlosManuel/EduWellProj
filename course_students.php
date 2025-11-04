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

$course_id = intval($_GET['id'] ?? 0);

if ($course_id === 0) {
    header("Location: teacher_dashboard.php");
    exit();
}

// Verify course belongs to teacher
$stmt = $conn->prepare("SELECT id, course_code, course_name FROM courses WHERE id = ? AND teacher_id = ?");
$stmt->bind_param("ii", $course_id, $user_id);
$stmt->execute();
$course_result = $stmt->get_result();
$stmt->close();

if ($course_result->num_rows === 0) {
    header("Location: teacher_dashboard.php");
    exit();
}

$course = $course_result->fetch_assoc();

$error = '';
$success = '';

// Handle add student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
    $student_email = trim($_POST['student_email'] ?? '');
    
    if (empty($student_email)) {
        $error = "Student email is required.";
    } else {
        // Find student by email
        $find_stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND role = 'student'");
        $find_stmt->bind_param("s", $student_email);
        $find_stmt->execute();
        $student_result = $find_stmt->get_result();
        
        if ($student_result->num_rows === 0) {
            $error = "No student found with that email address.";
        } else {
            $student = $student_result->fetch_assoc();
            $student_id = $student['id'];
            
            // Check if already enrolled
            $check_stmt = $conn->prepare("SELECT id FROM enrollments WHERE student_id = ? AND course_id = ?");
            $check_stmt->bind_param("ii", $student_id, $course_id);
            $check_stmt->execute();
            
            if ($check_stmt->get_result()->num_rows > 0) {
                $error = "Student is already enrolled in this course.";
            } else {
                $enroll_stmt = $conn->prepare("INSERT INTO enrollments (student_id, course_id) VALUES (?, ?)");
                $enroll_stmt->bind_param("ii", $student_id, $course_id);
                
                if ($enroll_stmt->execute()) {
                    $success = "Student enrolled successfully!";
                    $_POST['student_email'] = '';
                } else {
                    $error = "Failed to enroll student. Please try again.";
                }
                $enroll_stmt->close();
            }
            $check_stmt->close();
        }
        $find_stmt->close();
    }
}

// Handle remove student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_student'])) {
    $student_id = intval($_POST['student_id'] ?? 0);
    
    if ($student_id > 0) {
        $remove_stmt = $conn->prepare("DELETE FROM enrollments WHERE student_id = ? AND course_id = ?");
        $remove_stmt->bind_param("ii", $student_id, $course_id);
        
        if ($remove_stmt->execute()) {
            $success = "Student removed from course.";
        } else {
            $error = "Failed to remove student.";
        }
        $remove_stmt->close();
    }
}

// Get enrolled students
$stmt = $conn->prepare("
    SELECT u.id, u.name, u.email, COUNT(DISTINCT g.id) as graded_count, COUNT(DISTINCT a.id) as total_assignments,
           AVG(g.percentage) as avg_score
    FROM enrollments e
    JOIN users u ON e.student_id = u.id
    LEFT JOIN assignments a ON a.course_id = ?
    LEFT JOIN grades g ON g.assignment_id = a.id AND g.student_id = u.id
    WHERE e.course_id = ?
    GROUP BY u.id, u.name, u.email
    ORDER BY u.name ASC
");
$stmt->bind_param("ii", $course_id, $course_id);
$stmt->execute();
$students = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - EduWell</title>
    <link rel="stylesheet" href="css/yawa.css">
    <style>
        .manage-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
        }
        .course-header {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .add-student-form {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .form-group {
            display: flex;
            gap: 10px;
        }
        .form-group input {
            flex: 1;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .form-group button {
            padding: 12px 24px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .form-group button:hover {
            background: #0056b3;
        }
        .students-table {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #f8f9fa;
            font-weight: bold;
        }
        .btn-danger {
            padding: 6px 12px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-danger:hover {
            background: #c82333;
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
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="manage-container">
        <div class="course-header">
            <h1><?= htmlspecialchars($course['course_code']) ?> - <?= htmlspecialchars($course['course_name']) ?></h1>
            <p><a href="manage_course.php?id=<?= $course_id ?>">← Back to Course Management</a></p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <div class="add-student-form">
            <h2>Add Student</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <input type="email" name="student_email" placeholder="Enter student email address" 
                           value="<?= htmlspecialchars($_POST['student_email'] ?? '') ?>" required>
                    <button type="submit" name="add_student">Add Student</button>
                </div>
            </form>
        </div>
        
        <div class="students-table">
            <h2>Enrolled Students (<?= $students->num_rows ?>)</h2>
            <?php if ($students->num_rows === 0): ?>
                <p style="padding: 20px; text-align: center; color: #666;">
                    No students enrolled yet. Add students using the form above.
                </p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Assignments Completed</th>
                            <th>Average Score</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($student = $students->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($student['name']) ?></strong></td>
                                <td><?= htmlspecialchars($student['email']) ?></td>
                                <td><?= $student['graded_count'] ?>/<?= $student['total_assignments'] ?></td>
                                <td>
                                    <?php if ($student['avg_score']): ?>
                                        <strong><?= number_format($student['avg_score'], 2) ?>%</strong>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" action="" style="display: inline;" 
                                          onsubmit="return confirm('Are you sure you want to remove this student from the course?');">
                                        <input type="hidden" name="student_id" value="<?= $student['id'] ?>">
                                        <button type="submit" name="remove_student" class="btn-danger">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>

