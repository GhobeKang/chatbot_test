<?php
use Longman\TelegramBot\Request;

$chat_id = $telegram->getChatId();

if ($photo[0]['file_size'] > 2*1024*1024) {
    Request::sendMessage($chat_id, 'You must upload a file less than 2Mb.');
    return false;
}

try {
    $file = getRemoteFilePathTelegram($photo[0]['file_id']);
    download_file_telegram('https://api.telegram.org/file/bot'. $bot_api_key . '/' . $file['path']);
    recog_face($telegram, $file);
} catch (Exception $e) {
    file_put_contents(__DIR__ . '/error_logs.txt', $e->getMessage());
}

$telegram->countUpTargetType($telegram->getChatId(), $telegram->getUserId(), 'act_photo_cnt');

function recog_face($telegram, $file) {
    $client_id = 'r4YMnumW1hvL1hEhO7QA';
    $client_secret = 'BvhspTz8Oz';
    $base_url = 'https://openapi.naver.com/v1/vision/face';
    $is_post = true;
    
    $ch = curl_init();

    $curl_file = curl_file_create(__DIR__ . '/../temp_image.jpeg', 'image/jpeg', $file['id'].'.jpeg');
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
            $img = file_get_contents(__DIR__ . '/../temp_image.jpeg');
            $img_base64 = base64_encode($img);
            delMsg($telegram, 'photo', $img_base64);
        }
        unlink(__DIR__ . '/../temp_image.jpeg');
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
function download_file_telegram ($url) {
    // Initialize the cURL session 
    $ch = curl_init($url);  
    
    // Use basename() function to return 
    // the base name of file  
    $file_name = 'temp_image.jpeg';
    
    // Save file into file location 
    $save_file_loc = __DIR__ . '/../' . $file_name; 
    
    // Open file  
    $fp = fopen($save_file_loc, 'wb'); 
    
    // It set an option for a cURL transfer 
    curl_setopt($ch, CURLOPT_FILE, $fp); 
    curl_setopt($ch, CURLOPT_HEADER, 0); 
    
    // Perform a cURL session 
    curl_exec($ch); 
    
    // Closes a cURL session and frees all resources 
    curl_close($ch); 
    
    // Close file 
    fclose($fp); 

}
function get_url_fsockopen( $url ) {
    $URL_parsed = parse_url($url);

    $host = $URL_parsed["host"];
    $port = $URL_parsed["port"];
    if ($port==0)
         $port = 80;

    $path = $URL_parsed["path"];
    if ($URL_parsed["query"] != "")
         $path .= "?".$URL_parsed["query"];

   $out = "GET $path HTTP/1.0rn";
   $out .= "Host: ".$host."rn";
   $out .= "Connection: Closernrn";

    $fp = fsockopen($host, $port, $errno, $errstr, 30);
    if (!$fp) {
         echo "$errstr ($errno)<br>n";
    } else {
         fputs($fp, $out);
         $body = false;
         while (!feof($fp)) {
         $s = fgets($fp, 128);
         if ( $body )
              $in .= $s;
         if ( $s == "rn" )
              $body = true;
         }

         fclose($fp);
         echo $in;
    }
}
?>