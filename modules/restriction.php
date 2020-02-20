<?php
    use Longman\TelegramBot\Request;

    $restriction_data = $telegram->get_restriction($chat_id, $caller_member_id);

    if ($msg_type === 'new') {
        $expire_time = time() + ($restriction_data[1] * 1000);
        $telegram->set_restriction($caller_member_id, $expire_time);
    }

    if ($restriction_data[0]) {
        if ($telegram->get_restriction_time($caller_member_id)) {
            $params = $telegram->getEditParams();

            $data = [
                'chat_id' => (string)$params['chat_id'],
                'message_id' => (string)$params['message_id']
            ];

            $result = Request::deleteMessage($data);
            
            return false;
        } else {
            $telegram->expire_restriction($caller_member_id);
            
            return false;
        }
    }
?>