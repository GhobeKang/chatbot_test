<?php
    use Longman\TelegramBot\Request;
    
    if (sizeof($faq_lists) !== 0) {
        $params = $telegram->getEditParams();
        foreach($faq_lists as $faq) {
            $keywords = explode(',', $faq['faq_content']);
            if (isset($faq['buttons']) && $faq['buttons'] !== null) {
                $keyboard = [
                    'inline_keyboard' => array(json_decode($faq['buttons'], true))
                ];
            } else {
                $keyboard = [];
            }
            $encodedKeyboard = json_encode($keyboard);    

            if ($faq['keyword_type'] === '1') {
                $keyword_pattern = '/';
                foreach($keywords as $index => $keyword) {
                    if ($index == sizeof($keywords) - 1) {
                        $keyword_pattern = $keyword_pattern.'(?=.*'.trim($keyword).')/m';
                    } else {
                        $keyword_pattern = $keyword_pattern.'(?=.*'.trim($keyword).')';
                    }
                }    

                if (preg_match($keyword_pattern, $text)) {
                    if ($faq['response_type'] === 'txt') {
                        Request::sendMessage(array('text' => $faq['faq_response'], 'chat_id' => $chat_id, 'reply_to_message_id' => $params['message_id'], 'reply_markup' => $encodedKeyboard));
                    } else if ($faq['response_type'] === 'img') {
                        $sp = Request::sendPhoto(array('chat_id' => $chat_id, 'photo' => $faq['faq_response_img'], 'reply_to_message_id' => $params['message_id'], 'reply_markup' => $encodedKeyboard));
    
                        if ($sp->isOk()) {
                            return true;
                        }
                        
                    }
                    $telegram->pushEventHistory($chat_id, 'faq');
                    return true;
                }
            }

            foreach($keywords as $keyword) {
                if (strpos($text, trim($keyword)) !== false) {
                    if ($faq['keyword_type'] === '0') {
                        if ($faq['response_type'] === 'txt') {
                            Request::sendMessage(array('text' => $faq['faq_response'], 'chat_id' => $chat_id, 'reply_to_message_id' => $params['message_id'], 'reply_markup' => $encodedKeyboard));
                        } else if ($faq['response_type'] === 'img') {
                            $sp = Request::sendPhoto(array('chat_id' => $chat_id, 'photo' => $faq['faq_response_img'], 'reply_to_message_id' => $params['message_id'], 'reply_markup' => $encodedKeyboard));
        
                            if ($sp->isOk()) {
                                return true;
                            }
                            
                        }
                        $telegram->pushEventHistory($chat_id, 'faq');
                        return true;
                    }
                }
            }
        }
    }
?>