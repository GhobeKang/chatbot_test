<?php
// Load composer
use Longman\TelegramBot\Request;

require __DIR__ . '/vendor/autoload.php';
$test_env = 1;

if ($test_env) {
    $bot_api_key  = '822428347:AAGXao7qTxCL5MoqQyeSqPc7opK607fA51I';
    $bot_username = 'aqoom_test_bot';
    $mysql_credentials = [
        'host'     => '127.0.0.1',
        'port'     => 3306, // optional
        'user'     => 'root',
        'password' => 'term!ner1',
        'database' => 'aqoom'
    ];
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
    $telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);
    $telegram->addCommandsPaths($commands_paths);
    $telegram->enableMySql($mysql_credentials);

    $forbidden_lists = array();
    
    // Handle telegram webhook request
    if ($telegram->handle()) {
        if ($telegram->progress_inlinequery()) {
            return;
        }

        $text = $telegram->getMessage();
        $chat_id = $telegram->getChatId();
        $photo = $telegram->getImage();
        $msg_type = $telegram->getMsgType();
        $is_bot = $telegram->getStateBot();
        $options = $telegram->getStateOptions($chat_id);
        $caller_member_id = $telegram->getUserId();

        include(__DIR__ . '/modules/init_hook.php');
        include(__DIR__ . '/modules/analytics_countup_for_group.php');
        
        $forbidden_lists = $telegram->getForbiddenLists($chat_id);
        $faq_lists = $telegram->getFaqLists($chat_id);

        $url_pattern = '/(https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|www\.[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9]+\.[^\s]{2,}|www\.[a-zA-Z0-9]+\.[^\s]{2,})|[a-zA-Z0-9]+\.[^\s]{2,}/';

        // if sending a photo
        if ($photo && $options['is_img_filter']) {
            include(__DIR__ . '/modules/process_face_filtering.php');
        }

        // if the message is matched with URL pattern
        if ($text && preg_match($url_pattern, $text)) {
            include(__DIR__ . '/modules/process_url_block.php');

        // common text messages
        } else {
            $telegram->countUpTargetType($chat_id, $telegram->getUserId(), 'act_txt_cnt');

            include(__DIR__ . '/modules/process_word_block.php');
            
            // if a message is matched with registered FAQ patten,
            include(__DIR__ . '/modules/process_faq.php');

            // collect user questions to will be used at marketing elements.
            include(__DIR__ . '/modules/market_collect_questions.php');

            // calculate score of user's activities.
            include(__DIR__ . '/modules/market_scoring.php');
        }
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
        return true;
    }
}


