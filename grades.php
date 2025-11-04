<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: Registration/signin_new.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get student's courses and grades
$stmt = $conn->prepare("
    SELECT c.id, c.course_code, c.course_name, c.semester, c.academic_year,
           COALESCE(AVG(g.percentage), 0) as avg_score,
           COUNT(DISTINCT a.id) as total_assignments,
           COUNT(DISTINCT g.id) as graded_assignments
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    LEFT JOIN assignments a ON a.course_id = c.id
    LEFT JOIN grades g ON g.assignment_id = a.id AND g.student_id = ?
    WHERE e.student_id = ?
    GROUP BY c.id, c.course_code, c.course_name, c.semester, c.academic_year
    ORDER BY c.course_code ASC
");
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$courses = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Grades - EduWell</title>
    <link rel="stylesheet" href="css/yawa.css">
    <style>
        .grades-container { 
            max-width: 1200px; 
            margin: 40px auto; 
            padding: 20px; 
        }
        .course-card { 
            background: white; 
            border-radius: 10px; 
            padding: 25px; 
            margin-bottom: 25px; 
            box-shadow: 0 2px 8px rgba(0,0,0,0.1); 
        }
        .course-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 20px; 
            flex-wrap: wrap;
        }
        .course-info h2 {
            margin: 0 0 5px 0;
            color: #007bff;
        }
        .course-info p {
            margin: 5px 0;
            color: #666;
            font-size: 14px;
        }
        .grade-summary { 
            text-align: right;
        }
        .grade-summary .avg-score {
            font-size: 32px; 
            font-weight: bold; 
            color: #007bff; 
            margin: 0;
        }
        .grade-summary .label {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
        .assignments-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
        }
        .assignments-table th, .assignments-table td { 
            padding: 12px; 
            text-align: left; 
            border-bottom: 1px solid #eee; 
        }
        .assignments-table th { 
            background: #f8f9fa; 
            font-weight: bold;
            color: #333;
        }
        .assignments-table tr:hover {
            background: #f8f9fa;
        }
        .progress-bar { 
            width: 100%; 
            height: 25px; 
            background: #e9ecef; 
            border-radius: 15px; 
            overflow: hidden; 
            margin-top: 15px; 
            margin-bottom: 20px;
        }
        .progress-fill { 
            height: 100%; 
            background: linear-gradient(90deg, #007bff, #0056b3); 
            transition: width 0.5s ease;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 10px;
            color: white;
            font-weight: bold;
            font-size: 12px;
        }
        .letter-grade {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 5px;
            font-weight: bold;
            font-size: 14px;
        }
        .grade-A { background: #28a745; color: white; }
        .grade-B { background: #17a2b8; color: white; }
        .grade-C { background: #ffc107; color: #333; }
        .grade-D { background: #fd7e14; color: white; }
        .grade-F { background: #dc3545; color: white; }
        .no-courses {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        .no-grades {
            text-align: center;
            padding: 20px;
            color: #999;
            font-style: italic;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="grades-container">
        <h1 style="margin-bottom: 30px;">My Grades</h1>
        
        <?php if ($courses->num_rows === 0): ?>
            <div class="course-card no-courses">
                <h2>No Courses Enrolled</h2>
                <p>You haven't been enrolled in any courses yet. Contact your teacher or administrator.</p>
            </div>
        <?php else: ?>
            <?php while ($course = $courses->fetch_assoc()): ?>
                <div class="course-card">
                    <div class="course-header">
                        <div class="course-info">
                            <h2><?= htmlspecialchars($course['course_code']) ?> - <?= htmlspecialchars($course['course_name']) ?></h2>
                            <?php if ($course['semester']): ?>
                                <p><?= htmlspecialchars($course['semester']) ?> <?= htmlspecialchars($course['academic_year'] ?? '') ?></p>
                            <?php endif; ?>
                            <p style="margin-top: 10px;">
                                <strong>Assignments:</strong> <?= $course['graded_assignments'] ?>/<?= $course['total_assignments'] ?>
                            </p>
                        </div>
                        <div class="grade-summary">
                            <p class="avg-score"><?= number_format($course['avg_score'], 2) ?>%</p>
                            <p class="label">Course Average</p>
                        </div>
                    </div>
                    
                    <?php if ($course['total_assignments'] > 0): ?>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?= min($course['avg_score'], 100) ?>%">
                                <?php if ($course['avg_score'] > 15): ?>
                                    <?= number_format($course['avg_score'], 1) ?>%
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Assignment details -->
                    <?php
                    $assignments_stmt = $conn->prepare("
                        SELECT a.id, a.title, a.max_score, a.assignment_type, a.due_date,
                               g.score, g.percentage, g.letter_grade, g.graded_at, g.feedback
                        FROM assignments a
                        LEFT JOIN grades g ON g.assignment_id = a.id AND g.student_id = ?
                        WHERE a.course_id = ?
                        ORDER BY a.created_at DESC
                    ");
                    $assignments_stmt->bind_param("ii", $user_id, $course['id']);
                    $assignments_stmt->execute();
                    $assignments = $assignments_stmt->get_result();
                    ?>
                    
                    <?php if ($assignments->num_rows > 0): ?>
                        <table class="assignments-table">
                            <thead>
                                <tr>
                                    <th>Assignment</th>
                                    <th>Type</th>
                                    <th>Score</th>
                                    <th>Percentage</th>
                                    <th>Grade</th>
                                    <th>Due Date</th>
                                    <th>Graded</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($assignment = $assignments->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($assignment['title']) ?></strong>
                                            <?php if ($assignment['feedback']): ?>
                                                <br><small style="color: #666;"><?= htmlspecialchars($assignment['feedback']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($assignment['assignment_type']) ?></td>
                                        <td>
                                            <?php if ($assignment['score'] !== null): ?>
                                                <?= number_format($assignment['score'], 2) ?>/<?= number_format($assignment['max_score'], 2) ?>
                                            <?php else: ?>
                                                <span style="color: #999;">Not graded</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($assignment['percentage'] !== null): ?>
                                                <?= number_format($assignment['percentage'], 2) ?>%
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($assignment['letter_grade']): ?>
                                                <span class="letter-grade grade-<?= $assignment['letter_grade'] ?>">
                                                    <?= $assignment['letter_grade'] ?>
                                                </span>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($assignment['due_date']): ?>
                                                <?= date('M d, Y', strtotime($assignment['due_date'])) ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($assignment['graded_at']): ?>
                                                <?= date('M d, Y', strtotime($assignment['graded_at'])) ?>
                                            <?php else: ?>
                                                <span style="color: #999;">Pending</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="no-grades">
                            <p>No assignments have been created for this course yet.</p>
                        </div>
                    <?php endif; ?>
                    <?php $assignments_stmt->close(); ?>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>

