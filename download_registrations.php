<?php
// Проверяем авторизацию (можно добавить проверку пароля или сессии)
$password = isset($_GET['password']) ? $_GET['password'] : '';
$valid_password = 'admin123'; // Замените на реальный пароль

if ($password !== $valid_password) {
    die('Доступ запрещен');
}

$registration_folder = 'registrations/';

// Проверяем существование папки
if (!file_exists($registration_folder)) {
    die('Папка регистраций не найдена');
}

// Создаем ZIP архив со всеми регистрациями
$zip = new ZipArchive();
$zip_filename = 'all_registrations_' . date('Y-m-d') . '.zip';

if ($zip->open($zip_filename, ZipArchive::CREATE) === TRUE) {
    // Добавляем все файлы регистраций
    $files = scandir($registration_folder);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            $zip->addFile($registration_folder . $file, $file);
        }
    }
    
    $zip->close();
    
    // Отправляем файл пользователю
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $zip_filename . '"');
    header('Content-Length: ' . filesize($zip_filename));
    readfile($zip_filename);
    
    // Удаляем временный файл
    unlink($zip_filename);
} else {
    die('Ошибка создания архива');
}
?>
