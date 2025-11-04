<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: Registration/signin_new.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get comprehensive analytics
$stmt = $conn->prepare("
    SELECT 
        COALESCE(AVG(g.percentage), 0) as overall_average,
        COUNT(DISTINCT g.id) as total_graded,
        COUNT(DISTINCT a.id) as total_assignments,
        COUNT(DISTINCT c.id) as total_courses,
        SUM(CASE WHEN g.percentage >= 90 THEN 1 ELSE 0 END) as excellent_count,
        SUM(CASE WHEN g.percentage >= 80 AND g.percentage < 90 THEN 1 ELSE 0 END) as good_count,
        SUM(CASE WHEN g.percentage >= 70 AND g.percentage < 80 THEN 1 ELSE 0 END) as average_count,
        SUM(CASE WHEN g.percentage >= 60 AND g.percentage < 70 THEN 1 ELSE 0 END) as passing_count,
        SUM(CASE WHEN g.percentage < 60 THEN 1 ELSE 0 END) as needs_improvement_count
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    LEFT JOIN assignments a ON a.course_id = c.id
    LEFT JOIN grades g ON g.assignment_id = a.id AND g.student_id = ?
    WHERE e.student_id = ?
");
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$analytics = $stmt->get_result()->fetch_assoc();

// Identify weak areas
$weak_stmt = $conn->prepare("
    SELECT c.id, c.course_code, c.course_name, AVG(g.percentage) as avg_score, COUNT(g.id) as grade_count
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    LEFT JOIN assignments a ON a.course_id = c.id
    LEFT JOIN grades g ON g.assignment_id = a.id AND g.student_id = ?
    WHERE e.student_id = ?
    GROUP BY c.id, c.course_code, c.course_name
    HAVING avg_score < 70 AND grade_count > 0
    ORDER BY avg_score ASC
");
$weak_stmt->bind_param("ii", $user_id, $user_id);
$weak_stmt->execute();
$weak_areas = $weak_stmt->get_result();

// Get strong areas
$strong_stmt = $conn->prepare("
    SELECT c.id, c.course_code, c.course_name, AVG(g.percentage) as avg_score, COUNT(g.id) as grade_count
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    LEFT JOIN assignments a ON a.course_id = c.id
    LEFT JOIN grades g ON g.assignment_id = a.id AND g.student_id = ?
    WHERE e.student_id = ?
    GROUP BY c.id, c.course_code, c.course_name
    HAVING avg_score >= 85 AND grade_count > 0
    ORDER BY avg_score DESC
");
$strong_stmt->bind_param("ii", $user_id, $user_id);
$strong_stmt->execute();
$strong_areas = $strong_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - EduWell</title>
    <link rel="stylesheet" href="css/yawa.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .analytics-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-card h3 {
            font-size: 36px;
            color: #007bff;
            margin: 0;
        }
        .stat-card p {
            color: #666;
            margin-top: 10px;
            font-size: 14px;
        }
        .analysis-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .analysis-card h2 {
            margin-top: 0;
            color: #007bff;
        }
        .analysis-card ul {
            list-style: none;
            padding: 0;
        }
        .analysis-card li {
            padding: 12px;
            margin-bottom: 10px;
            border-radius: 5px;
            border-left: 4px solid #007bff;
            background: #f8f9fa;
        }
        .recommendation {
            background: #fff3cd;
            border-left-color: #ffc107;
        }
        .weak-area {
            background: #f8d7da;
            border-left-color: #dc3545;
        }
        .strong-area {
            background: #d4edda;
            border-left-color: #28a745;
        }
        .chart-container {
            margin-top: 20px;
            max-height: 400px;
        }
        .grade-distribution {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        .grade-item {
            text-align: center;
            padding: 15px;
            border-radius: 8px;
            background: #f8f9fa;
        }
        .grade-item h4 {
            margin: 0;
            font-size: 24px;
            color: #007bff;
        }
        .grade-item p {
            margin: 5px 0 0 0;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="analytics-container">
        <h1>Performance Analytics</h1>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?= number_format($analytics['overall_average'], 2) ?>%</h3>
                <p>Overall Average</p>
            </div>
            <div class="stat-card">
                <h3><?= $analytics['total_courses'] ?></h3>
                <p>Courses Enrolled</p>
            </div>
            <div class="stat-card">
                <h3><?= $analytics['total_graded'] ?></h3>
                <p>Assignments Completed</p>
            </div>
            <div class="stat-card">
                <h3><?= $analytics['total_assignments'] ?></h3>
                <p>Total Assignments</p>
            </div>
            <div class="stat-card">
                <h3><?= $analytics['total_assignments'] > 0 ? number_format(($analytics['total_graded'] / $analytics['total_assignments']) * 100, 1) : 0 ?>%</h3>
                <p>Completion Rate</p>
            </div>
        </div>
        
        <div class="analysis-card">
            <h2>Grade Distribution</h2>
            <div class="grade-distribution">
                <div class="grade-item">
                    <h4><?= $analytics['excellent_count'] ?></h4>
                    <p>A (90-100%)</p>
                </div>
                <div class="grade-item">
                    <h4><?= $analytics['good_count'] ?></h4>
                    <p>B (80-89%)</p>
                </div>
                <div class="grade-item">
                    <h4><?= $analytics['average_count'] ?></h4>
                    <p>C (70-79%)</p>
                </div>
                <div class="grade-item">
                    <h4><?= $analytics['passing_count'] ?></h4>
                    <p>D (60-69%)</p>
                </div>
                <div class="grade-item">
                    <h4><?= $analytics['needs_improvement_count'] ?></h4>
                    <p>F (<60%)</p>
                </div>
            </div>
            
            <?php if ($analytics['total_graded'] > 0): ?>
                <div class="chart-container">
                    <canvas id="distributionChart"></canvas>
                </div>
                <script>
                    const ctx = document.getElementById('distributionChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: ['A (90-100%)', 'B (80-89%)', 'C (70-79%)', 'D (60-69%)', 'F (<60%)'],
                            datasets: [{
                                data: [
                                    <?= $analytics['excellent_count'] ?>,
                                    <?= $analytics['good_count'] ?>,
                                    <?= $analytics['average_count'] ?>,
                                    <?= $analytics['passing_count'] ?>,
                                    <?= $analytics['needs_improvement_count'] ?>
                                ],
                                backgroundColor: [
                                    '#28a745',
                                    '#17a2b8',
                                    '#ffc107',
                                    '#fd7e14',
                                    '#dc3545'
                                ]
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false
                        }
                    });
                </script>
            <?php endif; ?>
        </div>
        
        <?php if ($weak_areas->num_rows > 0): ?>
            <div class="analysis-card">
                <h2>⚠️ Areas Needing Attention</h2>
                <ul>
                    <?php while ($area = $weak_areas->fetch_assoc()): ?>
                        <li class="weak-area">
                            <strong><?= htmlspecialchars($area['course_code']) ?> - <?= htmlspecialchars($area['course_name']) ?></strong>
                            <br>Average Score: <?= number_format($area['avg_score'], 2) ?>%
                            <br><small>Consider seeking additional help or dedicating more study time to this course.</small>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if ($strong_areas->num_rows > 0): ?>
            <div class="analysis-card">
                <h2>⭐ Strong Performance Areas</h2>
                <ul>
                    <?php while ($area = $strong_areas->fetch_assoc()): ?>
                        <li class="strong-area">
                            <strong><?= htmlspecialchars($area['course_code']) ?> - <?= htmlspecialchars($area['course_name']) ?></strong>
                            <br>Average Score: <?= number_format($area['avg_score'], 2) ?>%
                            <br><small>Excellent work! Keep up the great performance.</small>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="analysis-card">
            <h2>📋 Personalized Recommendations</h2>
            <ul>
                <?php
                $recommendations = [];
                
                if ($analytics['overall_average'] < 70) {
                    $recommendations[] = "Your overall performance suggests you may benefit from additional study time and support. Consider meeting with your teachers or academic advisors.";
                }
                
                if ($analytics['needs_improvement_count'] > 0) {
                    $recommendations[] = "You have " . $analytics['needs_improvement_count'] . " assignment(s) below 60%. Focus on reviewing these areas and consider seeking help from teachers or tutors.";
                }
                
                if ($analytics['total_graded'] < 5 && $analytics['total_assignments'] > 0) {
                    $recommendations[] = "You have limited graded assignments. Stay consistent with completing all coursework to get a better picture of your performance.";
                }
                
                if ($analytics['total_assignments'] > 0 && ($analytics['total_graded'] / $analytics['total_assignments']) < 0.8) {
                    $recommendations[] = "Your completion rate is below 80%. Focus on submitting assignments on time to improve your overall performance.";
                }
                
                if ($analytics['excellent_count'] > 0 && $analytics['excellent_count'] >= ($analytics['total_graded'] * 0.5)) {
                    $recommendations[] = "Great job! You're performing excellently in many assignments. Keep up the excellent work!";
                }
                
                if (empty($recommendations)) {
                    $recommendations[] = "Continue maintaining your current study habits. You're doing well!";
                }
                
                foreach ($recommendations as $rec):
                ?>
                    <li class="recommendation"><?= htmlspecialchars($rec) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>

