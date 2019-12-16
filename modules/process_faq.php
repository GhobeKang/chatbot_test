<?php
    use Longman\TelegramBot\Request;
    
    if (sizeof($faq_lists) !== 0) {
        foreach($faq_lists as $faq) {
            if (strpos($text, $faq['faq_content']) !== false) {
                $params = $telegram->getEditParams();
                $keyboard = [
                    'inline_keyboard' => [
                        [
                            ['text' =>  '👍' . ' helpful', 'callback_data' => $faq['faq_id'] . ' 1'],
                            ['text' => '👎' . 'unhelpful', 'callback_data' => $faq['faq_id'] . ' 0']
                        ]
                    ]
                ];
                $encodedKeyboard = json_encode($keyboard);

                if ($faq['response_type'] === 'txt') {
                    Request::sendMessage(array('text' => $faq['faq_response'], 'chat_id' => $chat_id, 'reply_to_message_id' => $params['message_id'], 'reply_markup' => $encodedKeyboard));
                } else if ($faq['response_type'] === 'img') {
                    $sp = Request::sendPhoto(array('chat_id' => $chat_id, 'photo' => $faq['faq_response_img'], 'reply_to_message_id' => $params['message_id'], 'reply_markup' => $encodedKeyboard));

                    if ($sp->isOk()) {
                        return true;
                    }
                    
                }
                
                return true;
            }
        }
    }
?>