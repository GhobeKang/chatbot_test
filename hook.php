<?php
// Load composer
echo 'asdf';
use Longman\TelegramBot\Request;

require __DIR__ . '/vendor/autoload.php';

$bot_api_key  = '847825836:AAFv02ESsTVjnrzIomgdiVjBGWVw7CpN_Cg';
$bot_username = 'aqoom_bot';

$mysql_credentials = [
    'host'     => '34.97.24.74',
    'port'     => 3306, // optional
    'user'     => 'root',
    'password' => 'aq@@mServ!ce',
    'database' => 'aqoomchat',
 ];

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
        $text = $telegram->getMessage();
        $chat_id = $telegram->getChatId();
        $photo = $telegram->getImage();
        $msg_type = $telegram->getMsgType();
        $is_bot = $telegram->getStateBot();
        $options = $telegram->getStateOptions($chat_id);
        $caller_member_id = $telegram->getUserId();

        $chat_member = Request::getChatMember(array('chat_id' => $chat_id, 'user_id' => $caller_member_id));
        if ($chat_member->result->status === 'administrator' || $chat_member->result->status === 'creator') {
            $telegram->setStateAdmin($caller_member_id);
        }
        
        if ($is_bot && $options['is_block_bot']) {
            Request::kickChatMember(array('chat_id' => $chat_id, 'user_id' => $is_bot));
        }

        $is_valid = $telegram->getIsActive($chat_id);
        if (!$is_valid) {
            return false;
        }

        if ($msg_type === 'comeout' && $options['is_ordering_comeout']) {
            delMsg($telegram, $msg_type, '', $telegram->getBotName());
            return true;
        }

        
        $telegram->countUpEntireMsgs($chat_id);
        $isActivation = $telegram->getStateActivation($chat_id);
        if (!$isActivation) {
            $activation_code = sha1($chat_id.time());
            $telegram->setActivationCode($chat_id, $activation_code);
        }

        $forbidden_lists = $telegram->getForbiddenLists($chat_id);
        $faq_lists = $telegram->getFaqLists($chat_id);
        
        $url_pattern = '/(https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|www\.[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9]+\.[^\s]{2,}|www\.[a-zA-Z0-9]+\.[^\s]{2,})|[a-zA-Z0-9]+\.[^\s]{2,}/';
        
        // if sending a photo
        if ($photo && $options['is_img_filter']) {
            if ($photo[0]['file_size'] > 2*1024*1024) {
                Request::sendMessage($chat_id, 'You must upload a file less than 2Mb.');
                return false;
            }
            
            $file = getRemoteFilePathTelegram($photo[0]['file_id']);
            file_put_contents('temp_image.jpeg', fopen('https://api.telegram.org/file/bot847825836:AAFv02ESsTVjnrzIomgdiVjBGWVw7CpN_Cg/'.$file['path'], 'r'));

            recog_face($telegram, $file);
            $telegram->countUpTargetType($telegram->getChatId(), $telegram->getUserId(), 'act_photo_cnt');
        }

        // if the message is matched with URL pattern
        if ($text && preg_match($url_pattern, $text)) {
            $whitelist = $telegram->getWhitelist($chat_id);
            $telegram->countUpTargetType($chat_id, $telegram->getUserId(), 'act_url_cnt');

            $state = true;

            foreach($whitelist as $url) {
                $regx = '/' . preg_quote($url, '/') . '/';
                if (preg_match('/^\/[\s\S]+\/$/', $url)) {
                    $regx = $url;
                }

                if (preg_match($regx, $text)) {
                    $state = false;
                }
            }

            if ($state) {
                delMsg($telegram, 'url');
                return true;
            }

            // common text messages
        } else {
            $telegram->countUpTargetType($chat_id, $telegram->getUserId(), 'act_txt_cnt');

            if ($forbidden_lists) {
                foreach($forbidden_lists as $word) {
                    if (strpos($text, $word) !== false) {
                        delMsg($telegram, 'text');
                        return true;
                    }
                }
            }
            
            // if a message is matched with registered FAQ patten,
            if (sizeof($faq_lists) !== 0) {
                foreach($faq_lists as $faq) {
                    if (strpos($text, $faq['faq_content']) !== false) {
                        if ($faq['response_type'] === 'txt') {
                            Request::sendMessage(array('text' => $faq['faq_response'], 'chat_id' => $chat_id));
                        } else if ($faq['response_type'] === 'img') {
                            $sp = Request::sendPhoto(array('chat_id' => $chat_id, 'photo' => $faq['faq_response_img']));

                            if ($sp->isOk()) {
                                return true;
                            }
                            
                        }
                        
                        return true;
                    }
                }
            }
        }
    }
} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    // Silence is golden!
    // log telegram errors
    // echo $e->getMessage();
    $chat_id = $telegram->getChatId();
    Request::sendMessage(array('text' => $e->getMessage(), 'chat_id' => $chat_id));
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

function getRemoteFilePathTelegram($file_id) {
    $file = Request::getFile(array('file_id'=>$file_id));  
    $dataset = array(
        'path' => $file->result->file_path,
        'size' => $file->result->file_size,
        'id' => $file->result->file_id
    );
    return $dataset;
}

function recog_face($telegram, $file) {
    $client_id = 'r4YMnumW1hvL1hEhO7QA';
    $client_secret = 'BvhspTz8Oz';
    $base_url = 'https://openapi.naver.com/v1/vision/face';
    $is_post = true;
    
    $ch = curl_init();

    $curl_file = curl_file_create('temp_image.jpeg', 'image/jpeg', $file['id'].'.jpeg');
    $postvars = array("filename" => 'temp_img.jpeg', "image" => $curl_file);
    curl_setopt($ch, CURLOPT_URL, $base_url);
    curl_setopt($ch, CURLOPT_POST, $is_post);
    curl_setopt($ch, CURLOPT_INFILESIZE, $file['size']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postvars);
    curl_setopt($ch, CURLINFO_HEADER_OUT, true);
    $headers = array();
    $headers[] = "X-Naver-Client-Id: ".$client_id;
    $headers[] = "X-Naver-Client-Secret: ".$client_secret;
    $headers[] = "Content-Type:multipart/form-data";
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec ($ch);
    $error = curl_error($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close ($ch);
    
    if ($status_code == 200) {
        $response = json_decode($response);
        $isface = $response->info->faceCount;
        if ($isface > 0) {
            $img = file_get_contents('temp_image.jpeg');
            $img_base64 = base64_encode($img);
            delMsg($telegram, 'photo', $img_base64);
        }
        unlink('temp_image.jpeg');
    }
}