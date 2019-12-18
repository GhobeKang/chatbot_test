<?php
    $reg_question = '/\?/';
    $mentions = $telegram->getMentions($chat_id);
    $reg_mentions = '/(';
    foreach ($mentions as $index => $mention) {
        if ($index === sizeof($mentions) - 1) {
            $reg_mentions .= $mention['word_name'] . ')/';
            break;
        }
        $reg_mentions .= $mention['word_name'] . '|';
    }

    $MIN_LENGTH = 100;
    $INTERVAL_LENGTH = 10;
    $MID_PRIORITY_SCORE_WEIGHT = 0.3;
    $metched_score = 0;
    $length_score = 0;
    $default_score = 1;

    if (preg_match($reg_question, $text) || preg_match($reg_mentions, $text)) {
        $metched_score = 10;
    }

    if ($MIN_LENGTH < strlen($text)) {
        $diff = strlen($text) - $MIN_LENGTH;
        $length_score = $diff / $INTERVAL_LENGTH * $MID_PRIORITY_SCORE_WEIGHT;
        $telegram->countUpTargetType($chat_id, $caller_member_id, 'act_longtext');
    }

    $total_score = $metched_score + $length_score + $default_score;
    
    $telegram->setUserScore($total_score, $caller_member_id);
?>