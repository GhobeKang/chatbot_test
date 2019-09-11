<?php
// Load composer

use Longman\TelegramBot\Request;

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
    $forbidden_lists = array();

    // Handle telegram webhook request
    if ($telegram->handle()) {
        $telegram->enableMySql($mysql_credentials);

        $text = $telegram->getMessage();
        $sql = "select * from forb_wordlist where 1";
        $forbidden_lists = $telegram->getForbiddenLists($sql);
        if ($forbidden_lists) {
            foreach($forbidden_lists as $word) {
                if (strpos($text, $word) !== false) {
                    $params = $telegram->getEditParams();

                    $data = [
                        'chat_id' => (string)$params['chat_id'],
                        'message_id' => (string)$params['message_id']
                    ];
                    $result = Request::deleteMessage($data);
                    if ($result->isOk()) {
                        // send announce message
                    }
                }
            }
        }
        // $telegram->handleGetUpdates();
    }
} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    // Silence is golden!
    // log telegram errors
    // echo $e->getMessage();
}