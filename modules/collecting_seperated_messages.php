<?php
    $question_pattern = '/\?/';
    $mention_admin_pattern = '/(admin|administrator|owner|어드민|관리자|manager)/';

    if (preg_match($question_pattern, $text)) {
        $telegram->countUpQuestions($chat_id, $caller_member_id);
    }
    if (preg_match($mention_admin_pattern, $text)) {
        $telegram->markUpMention($chat_id);
    }
?>