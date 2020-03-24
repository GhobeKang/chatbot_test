<?php
    use Longman\TelegramBot\Request;

    if ($msg_type === 'new') {
        $expire_time = time() + (30 * 60 * 1000);
        $telegram->set_restriction($caller_member_id, $expire_time);
        return false;
    }
    
    $restriction_data = $telegram->get_restriction($chat_id, $caller_member_id);

    if ($restriction_data[0]) { // is_new = 1 ?
        if ($telegram->get_restriction_time($caller_member_id)) { // return boolean
            $params = $telegram->getEditParams();

            $data = [
                'chat_id' => (string)$params['chat_id'],
                'message_id' => (string)$params['message_id']
            ];

            $result = delMsg($telegram, $msg_type);
            
            return false;
        } else {
            $telegram->expire_restriction($caller_member_id);
            
            return false;
        }
    }
?>