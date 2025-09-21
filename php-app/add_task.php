<?php
require_once 'config.php';

// Fungsi untuk membuat tugas baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $mata_kuliah = $_POST['mata_kuliah'];
    $dosen = $_POST['dosen'];
    $tugas = $_POST['tugas'];
    $level_kesulitan = $_POST['level_kesulitan'];
    $status = $_POST['status'];
    $tempat_pengumpulan = $_POST['tempat_pengumpulan'];
    $notes = $_POST['notes'];

    $stmt = $pdo->prepare("INSERT INTO tasks (mata_kuliah, dosen, tugas, level_kesulitan, status, tempat_pengumpulan, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$mata_kuliah, $dosen, $tugas, $level_kesulitan, $status, $tempat_pengumpulan, $notes]);
    
    // Redirect ke halaman daftar tugas dengan pesan sukses
    header('Location: tasks_list.php?success=1');
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Task - Task Manager</title>
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
            max-width: 800px;
            margin: 0 auto;
        }

        .form-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .form-header h2 {
            color: #333;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .form-header p {
            color: #666;
            font-size: 16px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        label {
            display: block;
            color: #333;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        input[type="text"], select, textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        input[type="text"]:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        select {
            cursor: pointer;
        }

        textarea {
            resize: vertical;
            min-height: 120px;
        }

        .difficulty-selector {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-top: 8px;
        }

        .difficulty-option {
            display: none;
        }

        .difficulty-label {
            padding: 12px;
            text-align: center;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }

        .difficulty-option:checked + .difficulty-label {
            border-color: #667eea;
            background: #667eea;
            color: white;
        }

        .difficulty-label.low { border-color: #28a745; }
        .difficulty-option:checked + .difficulty-label.low { background: #28a745; }

        .difficulty-label.medium { border-color: #ffc107; }
        .difficulty-option:checked + .difficulty-label.medium { background: #ffc107; color: #333; }

        .difficulty-label.high { border-color: #fd7e14; }
        .difficulty-option:checked + .difficulty-label.high { background: #fd7e14; }

        .difficulty-label.urgent { border-color: #dc3545; }
        .difficulty-option:checked + .difficulty-label.urgent { background: #dc3545; }

        .status-selector {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-top: 8px;
        }

        .status-option {
            display: none;
        }

        .status-label {
            padding: 12px;
            text-align: center;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            font-size: 14px;
        }

        .status-option:checked + .status-label {
            border-color: #667eea;
            background: #667eea;
            color: white;
        }

        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            margin-top: 20px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .btn:active {
            transform: translateY(0);
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .difficulty-selector {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .status-selector {
                grid-template-columns: 1fr;
            }
            
            .navbar {
                flex-direction: column;
                gap: 15px;
            }
            
            .navbar .nav-links {
                flex-wrap: wrap;
                justify-content: center;
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
            <a href="add_task.php" class="active">
                <i class="fas fa-plus"></i> Add Task
            </a>
            <a href="tasks_list.php">
                <i class="fas fa-list"></i> Task List
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="form-card">
            <div class="form-header">
                <h2><i class="fas fa-plus-circle"></i> Add New Task</h2>
                <p>Fill out the form below to add a new task to the system</p>
            </div>

            <form action="add_task.php" method="POST">
                <input type="hidden" name="action" value="create">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="mata_kuliah"><i class="fas fa-book"></i> Subject</label>
                        <input type="text" id="mata_kuliah" name="mata_kuliah" required placeholder="Example: Web Programming">
                    </div>
                    <div class="form-group">
                        <label for="dosen"><i class="fas fa-user-tie"></i> Lecturer</label>
                        <input type="text" id="dosen" name="dosen" required placeholder="Lecturer name">
                    </div>
                </div>

                <div class="form-group">
                    <label for="tugas"><i class="fas fa-clipboard-list"></i> Task</label>
                    <input type="text" id="tugas" name="tugas" required placeholder="Description of the task to be completed">
                </div>

                <div class="form-group">
                    <label><i class="fas fa-chart-line"></i> Difficulty Level</label>
                    <div class="difficulty-selector">
                        <input type="radio" id="low" name="level_kesulitan" value="low" class="difficulty-option" required>
                        <label for="low" class="difficulty-label low">Low</label>
                        
                        <input type="radio" id="medium" name="level_kesulitan" value="medium" class="difficulty-option">
                        <label for="medium" class="difficulty-label medium">Medium</label>
                        
                        <input type="radio" id="high" name="level_kesulitan" value="high" class="difficulty-option">
                        <label for="high" class="difficulty-label high">High</label>
                        
                        <input type="radio" id="urgent" name="level_kesulitan" value="urgent" class="difficulty-option">
                        <label for="urgent" class="difficulty-label urgent">Urgent</label>
                    </div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-tasks"></i> Status</label>
                    <div class="status-selector">
                        <input type="radio" id="not_done" name="status" value="not_done" class="status-option" required>
                        <label for="not_done" class="status-label">Not Done</label>
                        
                        <input type="radio" id="still_working" name="status" value="still_working_on_it" class="status-option">
                        <label for="still_working" class="status-label">Still Working</label>
                        
                        <input type="radio" id="done" name="status" value="done" class="status-option">
                        <label for="done" class="status-label">Done</label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="tempat_pengumpulan"><i class="fas fa-upload"></i> Submission Platform</label>
                    <select id="tempat_pengumpulan" name="tempat_pengumpulan" required>
                        <option value="">Select submission platform</option>
                        <option value="GCR">Google Classroom (GCR)</option>
                        <option value="google_drive">Google Drive</option>
                        <option value="hardfile">Hardfile</option>
                        <option value="vclass">VClass</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="notes"><i class="fas fa-sticky-note"></i> Notes</label>
                    <textarea id="notes" name="notes" placeholder="Additional notes or task details..."></textarea>
                </div>

                <button type="submit" class="btn">
                    <i class="fas fa-save"></i>
                    Save Task
                </button>
            </form>
        </div>
    </div>
</body>
</html>