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

// Get teacher's courses
$stmt = $conn->prepare("
    SELECT c.id, c.course_code, c.course_name, c.semester, c.academic_year,
           COUNT(DISTINCT e.student_id) as student_count,
           COUNT(DISTINCT a.id) as assignment_count
    FROM courses c
    LEFT JOIN enrollments e ON e.course_id = c.id
    LEFT JOIN assignments a ON a.course_id = c.id
    WHERE c.teacher_id = ?
    GROUP BY c.id, c.course_code, c.course_name, c.semester, c.academic_year
    ORDER BY c.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$courses = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - EduWell</title>
    <link rel="stylesheet" href="css/yawa.css">
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
        }
        .course-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .course-info h3 {
            margin: 0 0 10px 0;
            color: #007bff;
        }
        .course-info p {
            margin: 5px 0;
            color: #666;
            font-size: 14px;
        }
        .course-actions {
            display: flex;
            gap: 10px;
        }
        .course-actions button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-primary {
            background: #007bff;
            color: white;
        }
        .btn-primary:hover {
            background: #0056b3;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background: #545b62;
        }
        .quick-actions {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }
        .quick-actions button {
            padding: 15px 30px;
            font-size: 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            background: #007bff;
            color: white;
        }
        .quick-actions button:hover {
            background: #0056b3;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-card h3 {
            font-size: 32px;
            color: #007bff;
            margin: 0;
        }
        .stat-card p {
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="dashboard-container">
        <h1>Teacher Dashboard</h1>
        
        <?php
        // Get teacher stats
        $stats_stmt = $conn->prepare("
            SELECT 
                COUNT(DISTINCT c.id) as total_courses,
                COUNT(DISTINCT e.student_id) as total_students,
                COUNT(DISTINCT a.id) as total_assignments,
                COUNT(DISTINCT g.id) as total_grades
            FROM courses c
            LEFT JOIN enrollments e ON e.course_id = c.id
            LEFT JOIN assignments a ON a.course_id = c.id
            LEFT JOIN grades g ON g.assignment_id = a.id
            WHERE c.teacher_id = ?
        ");
        $stats_stmt->bind_param("i", $user_id);
        $stats_stmt->execute();
        $stats = $stats_stmt->get_result()->fetch_assoc();
        $stats_stmt->close();
        ?>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?= $stats['total_courses'] ?></h3>
                <p>Total Courses</p>
            </div>
            <div class="stat-card">
                <h3><?= $stats['total_students'] ?></h3>
                <p>Total Students</p>
            </div>
            <div class="stat-card">
                <h3><?= $stats['total_assignments'] ?></h3>
                <p>Total Assignments</p>
            </div>
            <div class="stat-card">
                <h3><?= $stats['total_grades'] ?></h3>
                <p>Grades Entered</p>
            </div>
        </div>
        
        <div class="quick-actions">
            <button onclick="window.location.href='create_course.php'">➕ Create New Course</button>
            <button onclick="window.location.href='add_assignment.php'">📝 Add Assignment</button>
        </div>
        
        <div class="card">
            <h2>My Courses</h2>
            <?php if ($courses->num_rows === 0): ?>
                <p style="padding: 20px; text-align: center; color: #666;">
                    You haven't created any courses yet. Click "Create New Course" to get started.
                </p>
            <?php else: ?>
                <?php while ($course = $courses->fetch_assoc()): ?>
                    <div class="course-card">
                        <div class="course-info">
                            <h3><?= htmlspecialchars($course['course_code']) ?> - <?= htmlspecialchars($course['course_name']) ?></h3>
                            <?php if ($course['semester']): ?>
                                <p><?= htmlspecialchars($course['semester']) ?> <?= htmlspecialchars($course['academic_year'] ?? '') ?></p>
                            <?php endif; ?>
                            <p>
                                <strong>Students:</strong> <?= $course['student_count'] ?> | 
                                <strong>Assignments:</strong> <?= $course['assignment_count'] ?>
                            </p>
                        </div>
                        <div class="course-actions">
                            <button class="btn-primary" onclick="window.location.href='manage_course.php?id=<?= $course['id'] ?>'">
                                Manage Course
                            </button>
                            <button class="btn-secondary" onclick="window.location.href='course_students.php?id=<?= $course['id'] ?>'">
                                View Students
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>

