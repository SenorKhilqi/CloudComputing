<?php
require_once 'config.php';

// Fungsi untuk memperbarui tugas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $id = $_POST['id'];
    $mata_kuliah = $_POST['mata_kuliah'];
    $dosen = $_POST['dosen'];
    $tugas = $_POST['tugas'];
    $level_kesulitan = $_POST['level_kesulitan'];
    $status = $_POST['status'];
    $tempat_pengumpulan = $_POST['tempat_pengumpulan'];
    $notes = $_POST['notes'];

    $stmt = $pdo->prepare("UPDATE tasks SET mata_kuliah = ?, dosen = ?, tugas = ?, level_kesulitan = ?, status = ?, tempat_pengumpulan = ?, notes = ? WHERE id = ?");
    $stmt->execute([$mata_kuliah, $dosen, $tugas, $level_kesulitan, $status, $tempat_pengumpulan, $notes, $id]);
    header('Location: tasks_list.php?updated=1');
    exit();
}

// Fungsi untuk menghapus tugas
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: tasks_list.php?deleted=1');
    exit();
}

// Filter berdasarkan status
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Query dengan filter
$sql = "SELECT * FROM tasks WHERE 1=1";
$params = [];

if ($filter_status && $filter_status !== 'all') {
    $sql .= " AND status = ?";
    $params[] = $filter_status;
}

if ($search) {
    $sql .= " AND (mata_kuliah LIKE ? OR dosen LIKE ? OR tugas LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY 
    CASE level_kesulitan 
        WHEN 'urgent' THEN 1 
        WHEN 'high' THEN 2 
        WHEN 'medium' THEN 3 
        WHEN 'low' THEN 4 
    END, 
    CASE status 
        WHEN 'not_done' THEN 1 
        WHEN 'still_working_on_it' THEN 2 
        WHEN 'done' THEN 3 
    END";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tasks = $stmt->fetchAll();

// Statistik
$stats_sql = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN status = 'not_done' THEN 1 ELSE 0 END) as not_done,
    SUM(CASE WHEN status = 'still_working_on_it' THEN 1 ELSE 0 END) as in_progress,
    SUM(CASE WHEN level_kesulitan = 'urgent' THEN 1 ELSE 0 END) as urgent
    FROM tasks";
$stats = $pdo->query($stats_sql)->fetch();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task List - Task Manager</title>
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

        .navbar .nav-links a.active {
            background: rgba(255, 255, 255, 0.3);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
        }

        .alert.success {
            background: rgba(40, 167, 69, 0.1);
            border: 1px solid rgba(40, 167, 69, 0.3);
            color: #28a745;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            border: 1px solid rgba(255, 255, 255, 0.18);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card .icon {
            font-size: 30px;
            margin-bottom: 10px;
        }

        .stat-card .number {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-card .label {
            color: #666;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-card.total { border-left: 4px solid #667eea; }
        .stat-card.total .icon { color: #667eea; }

        .stat-card.completed { border-left: 4px solid #28a745; }
        .stat-card.completed .icon { color: #28a745; }

        .stat-card.in-progress { border-left: 4px solid #ffc107; }
        .stat-card.in-progress .icon { color: #ffc107; }

        .stat-card.urgent { border-left: 4px solid #dc3545; }
        .stat-card.urgent .icon { color: #dc3545; }

        .filters-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        }

        .filters-form {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: end;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
        }

        .filter-group label {
            display: block;
            color: #333;
            font-weight: 600;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .filter-group input, .filter-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 14px;
            background: white;
        }

        .filter-buttons {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .tasks-grid {
            display: grid;
            gap: 20px;
        }

        .task-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            border: 1px solid rgba(255, 255, 255, 0.18);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .task-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .task-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
        }

        .task-card.difficulty-low::before { background: #28a745; }
        .task-card.difficulty-medium::before { background: #ffc107; }
        .task-card.difficulty-high::before { background: #fd7e14; }
        .task-card.difficulty-urgent::before { background: #dc3545; }

        .task-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }

        .task-title {
            font-size: 18px;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }

        .task-subject {
            color: #667eea;
            font-weight: 600;
            font-size: 14px;
        }

        .task-meta {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
            color: #666;
        }

        .badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge.status-not_done {
            background: rgba(108, 117, 125, 0.1);
            color: #6c757d;
        }

        .badge.status-still_working_on_it {
            background: rgba(255, 193, 7, 0.1);
            color: #856404;
        }

        .badge.status-done {
            background: rgba(40, 167, 69, 0.1);
            color: #155724;
        }

        .badge.difficulty-low {
            background: rgba(40, 167, 69, 0.1);
            color: #155724;
        }

        .badge.difficulty-medium {
            background: rgba(255, 193, 7, 0.1);
            color: #856404;
        }

        .badge.difficulty-high {
            background: rgba(253, 126, 20, 0.1);
            color: #a0522d;
        }

        .badge.difficulty-urgent {
            background: rgba(220, 53, 69, 0.1);
            color: #721c24;
        }

        .task-description {
            color: #666;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .task-notes {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 8px;
            border-left: 3px solid #667eea;
            margin-bottom: 15px;
            font-style: italic;
            color: #555;
        }

        .task-actions {
            display: flex;
            gap: 10px;
        }

        .btn-sm {
            padding: 8px 15px;
            font-size: 12px;
        }

        .btn-edit {
            background: #17a2b8;
            color: white;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        }

        .empty-state .icon {
            font-size: 60px;
            color: #ccc;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            color: #666;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #999;
            margin-bottom: 20px;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .modal-header h3 {
            color: #333;
            font-size: 20px;
        }

        .close {
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
            color: #999;
        }

        .close:hover {
            color: #333;
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
            
            .filters-form {
                flex-direction: column;
            }
            
            .filter-buttons {
                width: 100%;
                justify-content: stretch;
            }
            
            .btn {
                flex: 1;
                justify-content: center;
            }
            
            .task-header {
                flex-direction: column;
                gap: 10px;
            }
            
            .task-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1><i class="fas fa-tasks"></i> Task Manager</h1>
        <div class="nav-links">            <a href="dashboard.php">
                <i class="fas fa-home"></i> Beranda
            </a>
            <a href="add_task.php">
                <i class="fas fa-plus"></i> Add Task
            </a>
            <a href="tasks_list.php" class="active">
                <i class="fas fa-list"></i> Task List
            </a>
        </div>
    </nav>

    <div class="container">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert success">
                <i class="fas fa-check-circle"></i>
                Task successfully added!
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['updated'])): ?>
            <div class="alert success">
                <i class="fas fa-check-circle"></i>
                Task successfully updated!
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert success">
                <i class="fas fa-check-circle"></i>
                Task successfully deleted!
            </div>
        <?php endif; ?>

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
            <div class="stat-card in-progress">
                <div class="icon"><i class="fas fa-clock"></i></div>
                <div class="number"><?= $stats['in_progress'] + $stats['not_done'] ?></div>
                <div class="label">Incomplete</div>
            </div>
            <div class="stat-card urgent">
                <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="number"><?= $stats['urgent'] ?></div>
                <div class="label">Urgent</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-card">
            <form method="GET" class="filters-form">
                <div class="filter-group">
                    <label for="search">Search Tasks</label>
                    <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search for subject, lecturer, or task...">
                </div>
                <div class="filter-group">
                    <label for="status">Filter Status</label>
                    <select id="status" name="status">
                        <option value="all" <?= $filter_status === 'all' || $filter_status === '' ? 'selected' : '' ?>>All Status</option>
                        <option value="not_done" <?= $filter_status === 'not_done' ? 'selected' : '' ?>>Not Done</option>
                        <option value="still_working_on_it" <?= $filter_status === 'still_working_on_it' ? 'selected' : '' ?>>Still Working</option>
                        <option value="done" <?= $filter_status === 'done' ? 'selected' : '' ?>>Done</option>
                    </select>
                </div>
                <div class="filter-buttons">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="tasks_list.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Tasks List -->
        <div class="tasks-grid">
            <?php if (empty($tasks)): ?>
                <div class="empty-state">
                    <div class="icon"><i class="fas fa-clipboard-list"></i></div>
                    <h3>No tasks yet</h3>
                    <p>Click the "Add Task" button to create your first task</p>
                    <a href="add_task.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add First Task
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($tasks as $task): ?>
                    <div class="task-card difficulty-<?= $task['level_kesulitan'] ?>">
                        <div class="task-header">
                            <div>
                                <div class="task-title"><?= htmlspecialchars($task['tugas']) ?></div>
                                <div class="task-subject"><?= htmlspecialchars($task['mata_kuliah']) ?></div>
                            </div>
                            <div>
                                <span class="badge status-<?= $task['status'] ?>">
                                    <?= $task['status'] === 'not_done' ? 'Not Done' : 
                                        ($task['status'] === 'still_working_on_it' ? 'In Progress' : 'Done') ?>
                                </span>
                            </div>
                        </div>

                        <div class="task-meta">
                            <div class="meta-item">
                                <i class="fas fa-user-tie"></i>
                                <?= htmlspecialchars($task['dosen']) ?>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-chart-line"></i>
                                <span class="badge difficulty-<?= $task['level_kesulitan'] ?>">
                                    <?= ucfirst($task['level_kesulitan']) ?>
                                </span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-upload"></i>
                                <?= $task['tempat_pengumpulan'] === 'GCR' ? 'Google Classroom' : 
                                    ($task['tempat_pengumpulan'] === 'google_drive' ? 'Google Drive' : 
                                    ($task['tempat_pengumpulan'] === 'vclass' ? 'VClass' : 'Hardfile')) ?>
                            </div>
                        </div>

                        <?php if ($task['notes']): ?>
                            <div class="task-notes">
                                <i class="fas fa-sticky-note"></i>
                                <?= htmlspecialchars($task['notes']) ?>
                            </div>
                        <?php endif; ?>

                        <div class="task-actions">
                            <button class="btn btn-edit btn-sm" onclick="openEditModal(<?= $task['id'] ?>)">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <a href="tasks_list.php?action=delete&id=<?= $task['id'] ?>" 
                               class="btn btn-delete btn-sm"
                               onclick="return confirm('Are you sure you want to delete this task?')">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> Edit Task</h3>
                <span class="close">&times;</span>
            </div>
            <form id="editForm" method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_id">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div>
                        <label>Subject</label>
                        <input type="text" name="mata_kuliah" id="edit_mata_kuliah" required>
                    </div>
                    <div>
                        <label>Lecturer</label>
                        <input type="text" name="dosen" id="edit_dosen" required>
                    </div>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label>Task</label>
                    <input type="text" name="tugas" id="edit_tugas" required>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div>
                        <label>Difficulty Level</label>
                        <select name="level_kesulitan" id="edit_level_kesulitan" required>
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                    <div>
                        <label>Status</label>
                        <select name="status" id="edit_status" required>
                            <option value="not_done">Not Done</option>
                            <option value="still_working_on_it">Still Working</option>
                            <option value="done">Done</option>
                        </select>
                    </div>
                    <div>
                        <label>Submission Place</label>
                        <select name="tempat_pengumpulan" id="edit_tempat_pengumpulan" required>
                            <option value="GCR">Google Classroom</option>
                            <option value="google_drive">Google Drive</option>
                            <option value="hardfile">Hardfile</option>
                            <option value="vclass">VClass</option>
                        </select>
                    </div>
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label>Notes</label>
                    <textarea name="notes" id="edit_notes" rows="3"></textarea>
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Task
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const tasks = <?= json_encode($tasks) ?>;
        const modal = document.getElementById('editModal');
        const closeBtn = document.querySelector('.close');

        function openEditModal(taskId) {
            const task = tasks.find(t => t.id == taskId);
            if (task) {
                document.getElementById('edit_id').value = task.id;
                document.getElementById('edit_mata_kuliah').value = task.mata_kuliah;
                document.getElementById('edit_dosen').value = task.dosen;
                document.getElementById('edit_tugas').value = task.tugas;
                document.getElementById('edit_level_kesulitan').value = task.level_kesulitan;
                document.getElementById('edit_status').value = task.status;
                document.getElementById('edit_tempat_pengumpulan').value = task.tempat_pengumpulan;
                document.getElementById('edit_notes').value = task.notes || '';
                
                modal.style.display = 'block';
            }
        }

        function closeEditModal() {
            modal.style.display = 'none';
        }

        closeBtn.onclick = closeEditModal;

        window.onclick = function(event) {
            if (event.target == modal) {
                closeEditModal();
            }
        }

        // Auto-hide alerts
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-20px)';
                setTimeout(() => alert.remove(), 300);
            });
        }, 3000);
    </script>
</body>
</html>