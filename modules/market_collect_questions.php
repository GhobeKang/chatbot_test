<?php
    $question_pattern = '/\?/';
    if (preg_match($question_pattern, $text)) {
        $telegram->countUpQuestions($chat_id, $telegram->getUserId());
    }
?>