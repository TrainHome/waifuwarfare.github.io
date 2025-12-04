<?php
header('Content-Type: application/json; charset=utf-8');

// Включить отображение ошибок для отладки
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Проверяем, что запрос POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Неверный метод запроса']);
    exit;
}

// Получаем данные из формы
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$platform = isset($_POST['platform']) ? trim($_POST['platform']) : '';
$region = isset($_POST['region']) ? trim($_POST['region']) : '';

// Валидация данных
if (empty($email) || empty($username) || empty($platform) || empty($region)) {
    echo json_encode(['success' => false, 'message' => 'Все поля обязательны для заполнения']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Некорректный email адрес']);
    exit;
}

// Форматируем данные для записи
$timestamp = date('Y-m-d H:i:s');
$data = [
    'timestamp' => $timestamp,
    'email' => $email,
    'username' => $username,
    'platform' => $platform,
    'region' => $region,
    'ip' => $_SERVER['REMOTE_ADDR']
];

// Формируем строку для записи
$entry = "=== Регистрация ===\n";
$entry .= "Дата/время: {$data['timestamp']}\n";
$entry .= "Email: {$data['email']}\n";
$entry .= "Имя игрока: {$data['username']}\n";
$entry .= "Платформа: {$data['platform']}\n";
$entry .= "Регион: {$data['region']}\n";
$entry .= "IP: {$data['ip']}\n";
$entry .= "==================\n\n";

// Путь к файлу
$filename = 'registration.txt';

// Проверяем, существует ли файл, если нет - создаем
if (!file_exists($filename)) {
    file_put_contents($filename, "=== СПИСОК ПРЕДРЕГИСТРАЦИЙ ===\n\n");
}

// Записываем данные в файл
if (file_put_contents($filename, $entry, FILE_APPEND | LOCK_EX)) {
    echo json_encode([
        'success' => true, 
        'message' => "Спасибо за предрегистрацию, командир {$username}! Мы отправили подтверждение на {$email}.",
        'data' => $data
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Ошибка при сохранении данных']);
}
?>
