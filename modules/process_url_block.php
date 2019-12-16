<?php
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
?>