<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: Registration/signin_new.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get student's performance across all courses
$stmt = $conn->prepare("
    SELECT c.id, c.course_code, c.course_name,
           AVG(g.percentage) as avg_score,
           COUNT(DISTINCT g.id) as total_grades,
           MAX(g.percentage) as highest_score,
           MIN(g.percentage) as lowest_score,
           COUNT(DISTINCT a.id) as total_assignments
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    LEFT JOIN assignments a ON a.course_id = c.id
    LEFT JOIN grades g ON g.assignment_id = a.id AND g.student_id = ?
    WHERE e.student_id = ?
    GROUP BY c.id, c.course_code, c.course_name
    ORDER BY avg_score DESC
");
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$reports = $stmt->get_result();

// Get overall statistics
$overall_stmt = $conn->prepare("
    SELECT 
        AVG(g.percentage) as overall_avg,
        COUNT(DISTINCT g.id) as total_graded,
        COUNT(DISTINCT c.id) as total_courses,
        MAX(g.percentage) as best_score,
        MIN(g.percentage) as worst_score
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    LEFT JOIN assignments a ON a.course_id = c.id
    LEFT JOIN grades g ON g.assignment_id = a.id AND g.student_id = ?
    WHERE e.student_id = ?
");
$overall_stmt->bind_param("ii", $user_id, $user_id);
$overall_stmt->execute();
$overall = $overall_stmt->get_result()->fetch_assoc();
$overall_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performance Reports - EduWell</title>
    <link rel="stylesheet" href="css/yawa.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .reports-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
        }
        .overall-stats {
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
        .course-report {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .trend-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
        }
        .trend-improving {
            background: #d4edda;
            color: #155724;
        }
        .trend-declining {
            background: #f8d7da;
            color: #721c24;
        }
        .trend-stable {
            background: #fff3cd;
            color: #856404;
        }
        .report-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        .report-stat-item {
            text-align: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .report-stat-item strong {
            display: block;
            font-size: 24px;
            color: #007bff;
            margin-bottom: 5px;
        }
        .chart-container {
            margin-top: 20px;
            max-height: 300px;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="reports-container">
        <h1>Performance Reports</h1>
        
        <div class="overall-stats">
            <div class="stat-card">
                <h3><?= $overall['overall_avg'] ? number_format($overall['overall_avg'], 2) : '0' ?>%</h3>
                <p>Overall Average</p>
            </div>
            <div class="stat-card">
                <h3><?= $overall['total_courses'] ?></h3>
                <p>Total Courses</p>
            </div>
            <div class="stat-card">
                <h3><?= $overall['total_graded'] ?></h3>
                <p>Assignments Graded</p>
            </div>
            <div class="stat-card">
                <h3><?= $overall['best_score'] ? number_format($overall['best_score'], 2) : 'N/A' ?>%</h3>
                <p>Best Score</p>
            </div>
            <div class="stat-card">
                <h3><?= $overall['worst_score'] ? number_format($overall['worst_score'], 2) : 'N/A' ?>%</h3>
                <p>Lowest Score</p>
            </div>
        </div>
        
        <?php if ($reports->num_rows === 0): ?>
            <div class="course-report">
                <p style="text-align: center; color: #666; padding: 40px;">
                    No performance data available yet. Grades will appear here once assignments are graded.
                </p>
            </div>
        <?php else: ?>
            <?php while ($report = $reports->fetch_assoc()): ?>
                <div class="course-report">
                    <div class="report-header">
                        <div>
                            <h2><?= htmlspecialchars($report['course_code']) ?> - <?= htmlspecialchars($report['course_name']) ?></h2>
                        </div>
                        <div>
                            <span style="font-size: 28px; font-weight: bold; color: #007bff;">
                                <?= number_format($report['avg_score'], 2) ?>%
                            </span>
                        </div>
                    </div>
                    
                    <?php
                    // Calculate trend
                    $trend_stmt = $conn->prepare("
                        SELECT g.percentage, g.graded_at
                        FROM grades g
                        JOIN assignments a ON g.assignment_id = a.id
                        WHERE g.student_id = ? AND a.course_id = ?
                        ORDER BY g.graded_at ASC
                    ");
                    $trend_stmt->bind_param("ii", $user_id, $report['id']);
                    $trend_stmt->execute();
                    $trends = $trend_stmt->get_result();
                    $trend_data = [];
                    while ($t = $trends->fetch_assoc()) {
                        $trend_data[] = $t['percentage'];
                    }
                    $trend_stmt->close();
                    
                    $trend = 'stable';
                    if (count($trend_data) > 1) {
                        $first_half = array_slice($trend_data, 0, ceil(count($trend_data)/2));
                        $second_half = array_slice($trend_data, ceil(count($trend_data)/2));
                        
                        if (count($first_half) > 0 && count($second_half) > 0) {
                            $first_avg = array_sum($first_half) / count($first_half);
                            $second_avg = array_sum($second_half) / count($second_half);
                            $diff = $second_avg - $first_avg;
                            
                            if ($diff > 2) $trend = 'improving';
                            elseif ($diff < -2) $trend = 'declining';
                        }
                    }
                    ?>
                    
                    <div class="report-stats">
                        <div class="report-stat-item">
                            <strong><?= number_format($report['avg_score'], 2) ?>%</strong>
                            <span>Average</span>
                        </div>
                        <div class="report-stat-item">
                            <strong><?= number_format($report['highest_score'], 2) ?>%</strong>
                            <span>Highest</span>
                        </div>
                        <div class="report-stat-item">
                            <strong><?= number_format($report['lowest_score'], 2) ?>%</strong>
                            <span>Lowest</span>
                        </div>
                        <div class="report-stat-item">
                            <strong><?= $report['total_grades'] ?>/<?= $report['total_assignments'] ?></strong>
                            <span>Completed</span>
                        </div>
                        <div class="report-stat-item">
                            <strong>
                                <span class="trend-badge trend-<?= $trend ?>">
                                    <?= ucfirst($trend) ?>
                                </span>
                            </strong>
                            <span>Trend</span>
                        </div>
                    </div>
                    
                    <?php if (count($trend_data) > 1): ?>
                        <div class="chart-container">
                            <canvas id="chart-<?= $report['id'] ?>"></canvas>
                        </div>
                        <script>
                            const ctx<?= $report['id'] ?> = document.getElementById('chart-<?= $report['id'] ?>').getContext('2d');
                            new Chart(ctx<?= $report['id'] ?>, {
                                type: 'line',
                                data: {
                                    labels: <?= json_encode(array_map(function($i) { return 'Assignment ' . ($i + 1); }, array_keys($trend_data))) ?>,
                                    datasets: [{
                                        label: 'Score (%)',
                                        data: <?= json_encode($trend_data) ?>,
                                        borderColor: '#007bff',
                                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                                        tension: 0.4
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    scales: {
                                        y: {
                                            beginAtZero: true,
                                            max: 100
                                        }
                                    }
                                }
                            });
                        </script>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>

