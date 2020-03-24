<?php
    if (isset($filter_options)) {
        if ($filter_options['anti_image']) {
            if (isset($photo) && $msg_type === 'image') {
                delMsg($telegram, 'img', $photo);
                return true;
            }
        }
        if ($filter_options['anti_url']) {
            if (preg_match($url_pattern, $text)) {
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
            }
            
        }
        if ($filter_options['anti_join_message']) {
            if ($msg_type === 'new') {
                delMsg($telegram, 'new');
                return true;
            }
        }
        if ($filter_options['anti_left_message']) {
            if ($msg_type === 'left') {
                delMsg($telegram, 'left');
                return true;
            }
        }
        if ($filter_options['anti_forward']) {
            if ($msg_type === 'forward') {
                delMsg($telegram, 'forward');
                return true;
            }
        }
        if ($filter_options['anti_gif']) {
            if ($msg_type === 'gif') {
                delMsg($telegram, 'gif');
                return true;
            }
        }
        if ($filter_options['anti_sticker']) {
            if ($msg_type === 'sticker') {
                delMsg($telegram, 'sticker');
                return true;
            }
        }
        if ($filter_options['anti_voice']) {
            if ($msg_type === 'voice') {
                delMsg($telegram, 'voice');
                return true;
            }
        }

        if ($filter_options['anti_slash']) {
            $slash_pattern = '/^\/[\s\S]+$/';
            if (preg_match($slash_pattern, $text)) {
                delMsg($telegram, 'command');
                return true;
            }
        }
    }
?>