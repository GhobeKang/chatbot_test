#!/usr/bin/env php
<?php
require __DIR__ . '/vendor/autoload.php';

$bot_api_key  = '1945387060:AAFhtD9o3tJVaj_x8f3v8Ztc9iZZZGEjJds';
$bot_username = 'aqoom_bot';

$mysql_credentials = [
   'host'     => '127.0.0.1',
   'port'     => 3306, // optional
   'user'     => 'root',
   'password' => 'term!ner1',
   'database' => 'aqoom',
];

try {
    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);

    // Enable MySQL
    $telegram->enableMySql($mysql_credentials);

    // Handle telegram getUpdates request
    $telegram->handleGetUpdates();
} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    // log telegram errors
    // echo $e->getMessage();
}