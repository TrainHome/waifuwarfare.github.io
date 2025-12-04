<?php
// Настройки
$registration_folder = 'registrations/';
$timestamp = date('Y-m-d_H-i-s');

// Создаем папку registrations, если ее нет
if (!file_exists($registration_folder)) {
    mkdir($registration_folder, 0777, true);
}

// Получаем данные из POST запроса
$email = isset($_POST['email']) ? htmlspecialchars(trim($_POST['email'])) : '';
$username = isset($_POST['username']) ? htmlspecialchars(trim($_POST['username'])) : '';
$platform = isset($_POST['platform']) ? htmlspecialchars(trim($_POST['platform'])) : '';
$region = isset($_POST['region']) ? htmlspecialchars(trim($_POST['region'])) : '';

// Валидация
if (empty($email) || empty($username) || empty($platform) || empty($region)) {
    echo json_encode(['success' => false, 'message' => 'Все поля обязательны для заполнения']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Некорректный email адрес']);
    exit;
}

// Создаем уникальное имя файла
$filename = $registration_folder . 'registration_' . $timestamp . '_' . uniqid() . '.txt';

// Содержимое файла
$content = "=== РЕГИСТРАЦИЯ В WAIYU WARFARE: TOWER TACTICS ===\n\n";
$content .= "Данные регистрации:\n";
$content .= "Дата/время: " . date('Y-m-d H:i:s') . "\n";
$content .= "Email: " . $email . "\n";
$content .= "Имя игрока: " . $username . "\n";
$content .= "Платформа: " . ($platform == 'pc' ? 'PC' : 'Mobile') . "\n";
$content .= "Регион: " . getRegionName($region) . "\n";
$content .= "IP адрес: " . $_SERVER['REMOTE_ADDR'] . "\n";
$content .= "User-Agent: " . $_SERVER['HTTP_USER_AGENT'] . "\n\n";
$content .= "=== КОНЕЦ РЕГИСТРАЦИИ ===\n";

// Записываем файл на сервер
if (file_put_contents($filename, $content)) {
    // Также добавляем запись в общий файл
    addToMasterFile($email, $username, $platform, $region);
    
    echo json_encode([
        'success' => true, 
        'message' => "Спасибо за предрегистрацию, командир $username! Данные сохранены на сервере.",
        'filename' => basename($filename)
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Ошибка при сохранении данных на сервер']);
}

// Функция для получения названия региона
function getRegionName($code) {
    $regions = [
        'eu' => 'Европа',
        'na' => 'Северная Америка',
        'asia' => 'Азия',
        'other' => 'Другой'
    ];
    return isset($regions[$code]) ? $regions[$code] : $code;
}

// Функция для добавления записи в общий файл
function addToMasterFile($email, $username, $platform, $region) {
    $master_file = 'registrations/all_registrations.txt';
    $entry = date('Y-m-d H:i:s') . " | " . $email . " | " . $username . " | " . 
              ($platform == 'pc' ? 'PC' : 'Mobile') . " | " . getRegionName($region) . "\n";
    
    if (file_exists($master_file)) {
        file_put_contents($master_file, $entry, FILE_APPEND | LOCK_EX);
    } else {
        $header = "=== ВСЕ РЕГИСТРАЦИИ WAIYU WARFARE ===\n";
        $header .= "Дата/время | Email | Имя игрока | Платформа | Регион\n";
        $header .= "--------------------------------------------------------\n";
        file_put_contents($master_file, $header . $entry);
    }
}
?>
