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
$stmt = $conn->prepare("SELECT id, course_code, course_name, semester, academic_year FROM courses WHERE id = ? AND teacher_id = ?");
$stmt->bind_param("ii", $course_id, $user_id);
$stmt->execute();
$course_result = $stmt->get_result();
$stmt->close();

if ($course_result->num_rows === 0) {
    header("Location: teacher_dashboard.php");
    exit();
}

$course = $course_result->fetch_assoc();

// Get assignments for this course
$assignments_stmt = $conn->prepare("
    SELECT a.id, a.title, a.assignment_type, a.max_score, a.due_date, 
           COUNT(DISTINCT g.id) as graded_count,
           COUNT(DISTINCT e.student_id) as total_students
    FROM assignments a
    LEFT JOIN enrollments e ON e.course_id = ?
    LEFT JOIN grades g ON g.assignment_id = a.id AND g.student_id = e.student_id
    WHERE a.course_id = ?
    GROUP BY a.id
    ORDER BY a.created_at DESC
");
$assignments_stmt->bind_param("ii", $course_id, $course_id);
$assignments_stmt->execute();
$assignments = $assignments_stmt->get_result();

// Get enrolled students
$students_stmt = $conn->prepare("
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
$students_stmt->bind_param("ii", $course_id, $course_id);
$students_stmt->execute();
$students = $students_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Course - EduWell</title>
    <link rel="stylesheet" href="css/yawa.css">
    <style>
        .manage-container {
            max-width: 1200px;
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
        .course-header h1 {
            margin: 0 0 10px 0;
            color: #007bff;
        }
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #eee;
        }
        .tab {
            padding: 12px 24px;
            background: none;
            border: none;
            border-bottom: 3px solid transparent;
            cursor: pointer;
            font-size: 16px;
            color: #666;
        }
        .tab.active {
            color: #007bff;
            border-bottom-color: #007bff;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .table-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
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
        tr:hover {
            background: #f8f9fa;
        }
        .btn-small {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary {
            background: #007bff;
            color: white;
        }
        .btn-primary:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="manage-container">
        <div class="course-header">
            <h1><?= htmlspecialchars($course['course_code']) ?> - <?= htmlspecialchars($course['course_name']) ?></h1>
            <?php if ($course['semester']): ?>
                <p><?= htmlspecialchars($course['semester']) ?> <?= htmlspecialchars($course['academic_year'] ?? '') ?></p>
            <?php endif; ?>
            <div style="margin-top: 15px;">
                <a href="add_assignment.php" class="btn-small btn-primary">➕ Add Assignment</a>
                <a href="course_students.php?id=<?= $course_id ?>" class="btn-small btn-primary">👥 Manage Students</a>
            </div>
        </div>
        
        <div class="tabs">
            <button class="tab active" onclick="showTab('assignments')">Assignments</button>
            <button class="tab" onclick="showTab('students')">Students</button>
        </div>
        
        <div id="assignments" class="tab-content active">
            <div class="table-container">
                <h2>Course Assignments</h2>
                <?php if ($assignments->num_rows === 0): ?>
                    <p style="padding: 20px; text-align: center; color: #666;">
                        No assignments created yet. <a href="add_assignment.php">Create your first assignment</a>
                    </p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Max Score</th>
                                <th>Due Date</th>
                                <th>Graded</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($assignment = $assignments->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($assignment['title']) ?></strong></td>
                                    <td><?= htmlspecialchars($assignment['assignment_type']) ?></td>
                                    <td><?= number_format($assignment['max_score'], 2) ?></td>
                                    <td><?= $assignment['due_date'] ? date('M d, Y', strtotime($assignment['due_date'])) : '-' ?></td>
                                    <td><?= $assignment['graded_count'] ?>/<?= $assignment['total_students'] ?></td>
                                    <td>
                                        <a href="add_grade.php?assignment_id=<?= $assignment['id'] ?>" class="btn-small btn-primary">
                                            Enter Grades
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
        
        <div id="students" class="tab-content">
            <div class="table-container">
                <h2>Enrolled Students</h2>
                <?php if ($students->num_rows === 0): ?>
                    <p style="padding: 20px; text-align: center; color: #666;">
                        No students enrolled yet. <a href="course_students.php?id=<?= $course_id ?>">Add students</a>
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
                                        <a href="student_grades.php?student_id=<?= $student['id'] ?>&course_id=<?= $course_id ?>" class="btn-small btn-primary">
                                            View Grades
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }
    </script>
    
    <?php include 'footer.php'; ?>
</body>
</html>

