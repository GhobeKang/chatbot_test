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

$commands_paths = [
    __DIR__ . '/Commands',
];

try {
    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);
    $telegram->addCommandsPaths($commands_paths);
    $telegram->enableAdmins([
        '873456015'
    ]);
    $telegram->enableMySql($mysql_credentials);

    $forbidden_lists = array();

    // Handle telegram webhook request
    if ($telegram->handle()) {
        $text = $telegram->getMessage();
        $chat_id = $telegram->getChatId();
        $photo = $telegram->getImage();
        $isActivation = $telegram->getStateActivation($chat_id);
        if (!$isActivation) {
            $activation_code = sha1($chat_id.time());
            $telegram->setActivationCode($chat_id, $activation_code);
        }

        $forbidden_lists = $telegram->getForbiddenLists("select * from forb_wordlist where chat_id=".$chat_id);
        $faq_lists = $telegram->getFaqLists("select * from faq_list where chat_id=".$chat_id);
        
        $url_pattern = '/(https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|www\.[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9]+\.[^\s]{2,}|www\.[a-zA-Z0-9]+\.[^\s]{2,})|[a-zA-Z0-9]+\.[^\s]{2,}/';
        if ($photo) {
            if ($photo[0]['file_size'] > 2*1024*1024) {
                Request::sendMessage($chat_id, 'You must upload a file less than 2Mb.');
                return false;
            }
            $file = Request::getFile(array('file_id'=>$photo[0]['file_id']));  
            $remote_file_path = $file->result->file_path;
            file_put_contents('temp_image.jpeg', fopen('https://api.telegram.org/file/bot847825836:AAFv02ESsTVjnrzIomgdiVjBGWVw7CpN_Cg/'.$remote_file_path, 'r'));

            $client_id = 'r4YMnumW1hvL1hEhO7QA';
            $client_secret = 'BvhspTz8Oz';
            $base_url = 'https://openapi.naver.com/v1/vision/face';
            $is_post = true;
            
            $ch = curl_init();

            $curl_file = curl_file_create('temp_image.jpeg', 'image/jpeg', $file->result->file_id.'.jpeg');
            $postvars = array("filename" => 'temp_img.jpeg', "image" => $curl_file);
            curl_setopt($ch, CURLOPT_URL, $base_url);
            curl_setopt($ch, CURLOPT_POST, $is_post);
            curl_setopt($ch, CURLOPT_INFILESIZE, $file->result->file_size);
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

            $headerSent = curl_getinfo($ch, CURLINFO_HEADER_OUT );
            echo $headerSent;
            echo "<br>[status_code]:".$status_code."<br>";
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

        if ($text && preg_match($url_pattern, $text)) {
            $whitelist = $telegram->getWhitelist($chat_id);

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

        } else {
            if ($forbidden_lists) {
                foreach($forbidden_lists as $word) {
                    if (strpos($text, $word) !== false) {
                        delMsg($telegram, 'text');
                        return true;
                    }
                }
            }
            
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
}

function delMsg($telegram, $type, $img='') {
    $params = $telegram->getEditParams();

    $data = [
        'chat_id' => (string)$params['chat_id'],
        'message_id' => (string)$params['message_id']
    ];
    $result = Request::deleteMessage($data);
    if ($result->isOk()) {
        // send announce message
        $telegram->delMessage($data['message_id'], $data['chat_id'], $type, $img);
        return true;
    }
}