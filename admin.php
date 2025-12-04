<?php
// Простая проверка пароля
$password = isset($_POST['password']) ? $_POST['password'] : '';
$valid_password = 'admin123'; // Замените на реальный пароль

$is_authenticated = false;

if ($password === $valid_password) {
    $is_authenticated = true;
    setcookie('admin_auth', md5($password . 'salt'), time() + 3600); // Кука на 1 час
} elseif (isset($_COOKIE['admin_auth']) && $_COOKIE['admin_auth'] === md5($valid_password . 'salt')) {
    $is_authenticated = true;
}

if (!$is_authenticated && $password !== '') {
    $error = 'Неверный пароль';
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель - Waifu Warfare</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #000;
            color: white;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: rgba(42, 42, 42, 0.9);
            padding: 30px;
            border-radius: 15px;
            border: 1px solid rgba(255, 215, 0, 0.3);
        }
        h1 {
            color: #FFD700;
            text-align: center;
            margin-bottom: 30px;
        }
        .login-form {
            max-width: 400px;
            margin: 100px auto;
            text-align: center;
        }
        .login-form input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 8px;
            border: 1px solid #FFD700;
            background: rgba(0,0,0,0.5);
            color: white;
            font-size: 1rem;
        }
        .login-form button {
            background: linear-gradient(135deg, #FFD700, #E6C200);
            color: black;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            font-size: 1rem;
            width: 100%;
        }
        .error {
            color: #f44336;
            margin: 10px 0;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .stat-card {
            background: rgba(0,0,0,0.3);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            border: 1px solid rgba(255,215,0,0.2);
        }
        .stat-number {
            font-size: 2.5rem;
            color: #FFD700;
            font-weight: bold;
        }
        .stat-label {
            color: #FFE866;
            margin-top: 10px;
        }
        .actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin: 30px 0;
            flex-wrap: wrap;
        }
        .action-btn {
            background: linear-gradient(135deg, #FFD700, #E6C200);
            color: black;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .registrations-list {
            margin-top: 30px;
            max-height: 500px;
            overflow-y: auto;
        }
        .registration-item {
            background: rgba(0,0,0,0.3);
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            border: 1px solid rgba(255,215,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (!$is_authenticated): ?>
            <div class="login-form">
                <h1>Админ-панель</h1>
                <form method="POST">
                    <input type="password" name="password" placeholder="Введите пароль" required>
                    <button type="submit">Войти</button>
                    <?php if (isset($error)): ?>
                        <div class="error"><?php echo $error; ?></div>
                    <?php endif; ?>
                </form>
            </div>
        <?php else: ?>
            <h1>Админ-панель Waifu Warfare</h1>
            
            <?php
            // Получаем статистику
            $registration_folder = 'registrations/';
            $total_files = 0;
            $total_size = 0;
            
            if (file_exists($registration_folder)) {
                $files = scandir($registration_folder);
                foreach ($files as $file) {
                    if ($file != '.' && $file != '..') {
                        $total_files++;
                        $total_size += filesize($registration_folder . $file);
                    }
                }
            }
            
            // Читаем общий файл
            $master_file = 'registrations/all_registrations.txt';
            $last_registrations = [];
            if (file_exists($master_file)) {
                $lines = file($master_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                $last_registrations = array_slice($lines, -10); // Последние 10 записей
            }
            ?>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_files; ?></div>
                    <div class="stat-label">Всего регистраций</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo round($total_size / 1024, 2); ?> KB</div>
                    <div class="stat-label">Общий размер</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo date('d.m.Y'); ?></div>
                    <div class="stat-label">Дата</div>
                </div>
            </div>
            
            <div class="actions">
                <a href="download_registrations.php?password=admin123" class="action-btn">
                    <i class="fas fa-download"></i> Скачать все регистрации
                </a>
                <a href="registrations/all_registrations.txt" target="_blank" class="action-btn">
                    <i class="fas fa-file-alt"></i> Просмотреть общий файл
                </a>
                <button onclick="location.reload()" class="action-btn">
                    <i class="fas fa-sync-alt"></i> Обновить
                </button>
            </div>
            
            <?php if (!empty($last_registrations)): ?>
                <div class="registrations-list">
                    <h2>Последние регистрации:</h2>
                    <?php foreach (array_reverse($last_registrations) as $reg): ?>
                        <div class="registration-item">
                            <?php echo htmlspecialchars($reg); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <div style="text-align: center; margin-top: 30px;">
                <a href="index.html" class="action-btn">Вернуться на сайт</a>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
