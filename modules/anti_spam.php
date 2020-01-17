<?php
    if (isset($filter_options)) {
        if ($filter_options['anti_image']) {
            if (isset($photo) && $msg_type === 'image') {
                delMsg($telegram, 'img', $photo);
            }
        }
        if ($filter_options['anti_url']) {
            if (preg_match($url_pattern, $text)) {
                delMsg($telegram, 'url');
            }
        }
        if ($filter_options['anti_join_message']) {
            if ($msg_type === 'new') {
                delMsg($telegram, 'new');
            }
        }
        if ($filter_options['anti_left_message']) {
            if ($msg_type === 'left') {
                delMsg($telegram, 'left');
            }
        }
        if ($filter_options['anti_forward']) {
            if ($msg_type === 'forward') {
                delMsg($telegram, 'forward');
            }
        }
        if ($filter_options['anti_gif']) {
            if ($msg_type === 'gif') {
                delMsg($telegram, 'gif');
            }
        }
        if ($filter_options['anti_sticker']) {
            if ($msg_type === 'sticker') {
                delMsg($telegram, 'sticker');
            }
        }
        if ($filter_options['anti_voice']) {
            if ($msg_type === 'voice') {
                delMsg($telegram, 'voice');
            }
        }
    }
?>