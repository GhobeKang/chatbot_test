#!/usr/bin/env php
<?php
require __DIR__ . '/vendor/autoload.php';

$bot_api_key  = '847825836:AAFv02ESsTVjnrzIomgdiVjBGWVw7CpN_Cg';
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