<?php
// Load composer
require __DIR__ . '/vendor/autoload.php';

$bot_api_key  = '1945387060:AAFhtD9o3tJVaj_x8f3v8Ztc9iZZZGEjJds';
$bot_username = 'aqoom_bot';
$hook_url     = 'https://373a2d74.ngrok.io';

try {
    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);

    // Set webhook
    $result = $telegram->setWebhook($hook_url, ['certificate' => './cert/cert.pem']);
    if ($result->isOk()) {
        echo $result->getDescription();
    }
} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    // log telegram errors
    // echo $e->getMessage();
}