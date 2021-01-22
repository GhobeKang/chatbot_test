<?php
// Load composer
use Longman\TelegramBot\Request;

require __DIR__ . '/vendor/autoload.php';
$test_env = 0;

if ($test_env) {
    $bot_api_key  = '822428347:AAGXao7qTxCL5MoqQyeSqPc7opK607fA51I';
    $bot_username = 'aqoom_test_bot';
    $mysql_credentials = [
        'host'     => '34.97.24.74',
        'port'     => 3306, // optional
        'user'     => 'root',
        'password' => 'aq@@mServ!ce',
        'database' => 'aqoomchat',
     ];
    // $mysql_credentials = [
    //     'host'     => '127.0.0.1',
    //     'port'     => 3306, // optional
    //     'user'     => 'root',
    //     'password' => 'term!ner1',
    //     'database' => 'aqoom'
    // ];
} else {
    $bot_api_key  = '847825836:AAFv02ESsTVjnrzIomgdiVjBGWVw7CpN_Cg';
    $bot_username = 'aqoom_bot';
    $mysql_credentials = [
        'host'     => '34.97.24.74',
        'port'     => 3306, // optional
        'user'     => 'root',
        'password' => 'aq@@mServ!ce',
        'database' => 'aqoomchat',
     ];
}

$commands_paths = [
    __DIR__ . '/Commands',
];

try {
    // Create Telegram API object
    date_default_timezone_set('Asia/Seoul'); 
    $telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);
    $telegram->addCommandsPaths($commands_paths);
    $telegram->enableMySql($mysql_credentials);

    $forbidden_lists = array();
    
    // Handle telegram webhook request
    if ($telegram->handle()) {
        $caller_member_id = $telegram->getUserId();
        $chat_id = $telegram->getChatId();

        if ($telegram->progress_inlinequery()) {
            return;
        }

        $chat_white_users = $telegram->getWhiteUsers($chat_id);
        foreach($chat_white_users as $user) {
            if ($user['user_id'] === $caller_member_id) {
                return false;
            }
        }

        $text = $telegram->getMessage();
        $photo = $telegram->getImage();
        $msg_type = $telegram->getMsgType();
        $is_bot = $telegram->getStateBot();
        $options = $telegram->getStateOptions($chat_id);

        include(__DIR__ . '/modules/init_hook.php');
        
        $forbidden_lists = $telegram->getForbiddenLists($chat_id);
        $faq_lists = $telegram->getFaqLists($chat_id);
        $filter_options = $telegram->getFilterOptions($chat_id);

        $url_pattern = '/(https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|www\.[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9]+\.[^\s]{2,}|www\.[a-zA-Z0-9]+\.[^\s]{2,})|[a-zA-Z0-9]+\.[^\s]{2,}/';

        include(__DIR__ . '/modules/anti_spam.php');
        // include(__DIR__ . '/modules/restriction.php');   

        include(__DIR__ . '/modules/analytics_countup.php');
        
        if ($msg_type === 'text') {
            // black list
            include(__DIR__ . '/modules/process_word_block.php');
            
            // if a message is matched with registered FAQ patten,
            include(__DIR__ . '/modules/process_faq.php');

            // calculate score of user's activities.
            // include(__DIR__ . '/modules/market_scoring.php');
        }

        // collect user questions to will be used at marketing elements.
        include(__DIR__ . '/modules/collecting_seperated_messages.php');
    
    }
} catch (Exception $e) {
    // Silence is golden!
    // log telegram errors
    $filename = 'error_logs.txt';
    file_put_contents($filename, $e->getMessage());
    
    Request::sendMessage(array('text' => $e->getMessage(), 'chat_id' => -182466928));
}

function delMsg($telegram, $type, $img='', $bot_name='') {
    $params = $telegram->getEditParams();
    $username = $telegram->getUserName();

    $data = [
        'chat_id' => (string)$params['chat_id'],
        'message_id' => (string)$params['message_id']
    ];
    $result = Request::deleteMessage($data);
    if ($result->isOk()) {
        // send announce message
        $telegram->delMessage($data['message_id'], $data['chat_id'], $type, $username, $img, $bot_name);
        $telegram->pushEventHistory($data['chat_id'], 'deleted');
        return true;
    }
}


