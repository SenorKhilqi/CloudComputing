<?php
require_once 'config.php';

// Get basic statistics for dashboard
try {
    $stats_sql = "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN status = 'not_done' THEN 1 ELSE 0 END) as not_done,
        SUM(CASE WHEN status = 'still_working_on_it' THEN 1 ELSE 0 END) as in_progress,
        SUM(CASE WHEN level_kesulitan = 'urgent' THEN 1 ELSE 0 END) as urgent
        FROM tasks";
    $stats = $pdo->query($stats_sql)->fetch();
    
    // Get recent tasks
    $recent_tasks_sql = "SELECT * FROM tasks ORDER BY id DESC LIMIT 3";
    $recent_tasks = $pdo->query($recent_tasks_sql)->fetchAll();
} catch (Exception $e) {
    $stats = ['total' => 0, 'completed' => 0, 'not_done' => 0, 'in_progress' => 0, 'urgent' => 0];
    $recent_tasks = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Manager - Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 15px 30px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        .navbar h1 {
            color: white;
            font-size: 24px;
            font-weight: 600;
        }

        .navbar .nav-links {
            display: flex;
            gap: 20px;
        }

        .navbar .nav-links a {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 25px;
            background: rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .navbar .nav-links a:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .hero-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 60px 40px;
            text-align: center;
            margin-bottom: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .hero-section h1 {
            font-size: 48px;
            color: #333;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .hero-section p {
            font-size: 18px;
            color: #666;
            margin-bottom: 40px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.6;
        }

        .cta-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-secondary {
            background: transparent;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
        }

        .stat-card:hover {
            transform: translateY(-10px);
        }

        .stat-card .icon {
            font-size: 48px;
            margin-bottom: 20px;
        }

        .stat-card .number {
            font-size: 36px;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .stat-card .label {
            color: #666;
            font-size: 16px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .stat-card.total::before { background: #667eea; }
        .stat-card.total .icon { color: #667eea; }

        .stat-card.completed::before { background: #28a745; }
        .stat-card.completed .icon { color: #28a745; }

        .stat-card.pending::before { background: #ffc107; }
        .stat-card.pending .icon { color: #ffc107; }

        .stat-card.urgent::before { background: #dc3545; }
        .stat-card.urgent .icon { color: #dc3545; }

        .recent-tasks {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .recent-tasks h2 {
            color: #333;
            font-size: 28px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .task-item {
            padding: 20px;
            border: 1px solid #e9ecef;
            border-radius: 15px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .task-item:hover {
            background: #f8f9fa;
            border-color: #667eea;
            transform: translateX(5px);
        }

        .task-info h4 {
            color: #333;
            margin-bottom: 5px;
            font-size: 16px;
        }

        .task-info p {
            color: #666;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .task-badges {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge.status-done {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }

        .badge.status-not_done {
            background: rgba(108, 117, 125, 0.1);
            color: #6c757d;
        }

        .badge.status-still_working_on_it {
            background: rgba(255, 193, 7, 0.1);
            color: #856404;
        }

        .badge.difficulty-urgent {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }

        .badge.difficulty-high {
            background: rgba(253, 126, 20, 0.1);
            color: #fd7e14;
        }

        .badge.difficulty-medium {
            background: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }

        .badge.difficulty-low {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .empty-state .icon {
            font-size: 80px;
            color: #ddd;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 24px;
            margin-bottom: 15px;
        }

        .empty-state p {
            font-size: 16px;
            margin-bottom: 30px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .feature-card .feature-icon {
            font-size: 40px;
            color: #667eea;
            margin-bottom: 20px;
        }

        .feature-card h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 20px;
        }

        .feature-card p {
            color: #666;
            line-height: 1.6;
        }

        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 15px;
            }
            
            .navbar .nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .hero-section {
                padding: 40px 20px;
            }
            
            .hero-section h1 {
                font-size: 36px;
            }
            
            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .btn {
                width: 100%;
                max-width: 300px;
                justify-content: center;
            }
            
            .task-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1><i class="fas fa-tasks"></i> Task Manager</h1>
        <div class="nav-links">
                        <a href="dashboard.php">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="add_task.php">
                <i class="fas fa-plus"></i> Add Task
            </a>
            <a href="tasks_list.php">
                <i class="fas fa-list"></i> Task List
            </a>
        </div>
    </nav>

    <div class="container">
        <!-- Hero Section -->
        <div class="hero-section">
            <h1><i class="fas fa-graduation-cap"></i> Task Manager</h1>
            <p>Manage your college tasks easily and efficiently. Track progress, priorities, and deadlines for all tasks in one well-organized place.</p>
            <div class="cta-buttons">
                <a href="add_task.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Task
                </a>
                <a href="tasks_list.php" class="btn btn-secondary">
                    <i class="fas fa-list"></i> View All Tasks
                </a>
            </div>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card total">
                <div class="icon"><i class="fas fa-tasks"></i></div>
                <div class="number"><?= $stats['total'] ?></div>
                <div class="label">Total Tasks</div>
            </div>
            <div class="stat-card completed">
                <div class="icon"><i class="fas fa-check-circle"></i></div>
                <div class="number"><?= $stats['completed'] ?></div>
                <div class="label">Completed</div>
            </div>
            <div class="stat-card pending">
                <div class="icon"><i class="fas fa-clock"></i></div>
                <div class="number"><?= $stats['in_progress'] + $stats['not_done'] ?></div>
                <div class="label">Pending</div>
            </div>
            <div class="stat-card urgent">
                <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="number"><?= $stats['urgent'] ?></div>
                <div class="label">Urgent</div>
            </div>
        </div>

        <!-- Features -->
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
                <h3>Task Priority</h3>
                <p>Set difficulty level and priority for each task from Low to Urgent for better time management</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-eye"></i></div>
                <h3>Progress Tracking</h3>
                <p>Monitor task completion status from "Not Done", "In Progress", to "Done" with ease</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-upload"></i></div>
                <h3>Multi Platform</h3>
                <p>Support various submission platforms like Google Classroom, Google Drive, VClass, and Hardfile</p>
            </div>
        </div>

        <!-- Recent Tasks -->
        <div class="recent-tasks">
            <h2><i class="fas fa-history"></i> Recent Tasks</h2>
            
            <?php if (empty($recent_tasks)): ?>
                <div class="empty-state">
                    <div class="icon"><i class="fas fa-clipboard-list"></i></div>
                    <h3>No tasks yet</h3>
                    <p>Start managing your college tasks by adding your first task</p>
                    <a href="add_task.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add First Task
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($recent_tasks as $task): ?>
                    <div class="task-item">
                        <div class="task-info">
                            <h4><?= htmlspecialchars($task['tugas']) ?></h4>
                            <p><i class="fas fa-book"></i> <?= htmlspecialchars($task['mata_kuliah']) ?> - <?= htmlspecialchars($task['dosen']) ?></p>
                            <div class="task-badges">
                                <span class="badge status-<?= $task['status'] ?>">
                                    <?= $task['status'] === 'not_done' ? 'Not Done' : 
                                        ($task['status'] === 'still_working_on_it' ? 'In Progress' : 'Done') ?>
                                </span>
                                <span class="badge difficulty-<?= $task['level_kesulitan'] ?>">
                                    <?= ucfirst($task['level_kesulitan']) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div style="text-align: center; margin-top: 30px;">
                    <a href="tasks_list.php" class="btn btn-primary">
                        <i class="fas fa-list"></i> View All Tasks
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>