<?php
    if (isset($msg_type)) {
        switch ($msg_type) {
            case 'left' : {
                $telegram->countup_for_group('analytics_user_left', $chat_id);
                break;
            }
            case 'new' : {
                $telegram->countup_for_group('analytics_user_new', $chat_id);
                break;
            }
            case 'image' : {
                $telegram->countup_for_group('analytics_count_img', $chat_id);
                break;
            }
            case 'gif' : {
                $telegram->countup_for_group('analytics_count_gif', $chat_id);
                break;
            }
            case 'sticker' : {
                $telegram->countup_for_group('analytics_count_sticker', $chat_id);
                break;
            }
            case 'forward' : {
                $telegram->countup_for_group('analytics_count_forward', $chat_id);
                break;
            }
            case 'text' : {
                $telegram->countup_for_group('analytics_count_text', $chat_id);
                break;
            }
            case 'poll' : {
                $telegram->countup_for_group('analytics_count_poll', $chat_id);
                break;
            }
            case 'survey' : {
                $telegram->countup_for_group('analytics_count_survey', $chat_id);
                break;
            }
        }
    }
?>