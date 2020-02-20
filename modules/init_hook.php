<?php
use Longman\TelegramBot\Request;

$chat_member = Request::getChatMember(array('chat_id' => $chat_id, 'user_id' => $caller_member_id));
if ($chat_member->result->status === 'administrator' || $chat_member->result->status === 'creator') {
    $telegram->setStateAdmin($caller_member_id);
    // return false;
}

if ($is_bot && $options['is_block_bot']) {
    Request::kickChatMember(array('chat_id' => $chat_id, 'user_id' => $is_bot));
}

$is_valid = $telegram->getIsActive($chat_id);
if (!$is_valid) {
    return false;
}

if ($msg_type === 'comeout' && $options['is_ordering_comeout']) {
    delMsg($telegram, $msg_type, '', $telegram->getBotName());
    return true;
}

$telegram->setAnalysisRow($chat_id);
$telegram->countUpEntireMsgs($chat_id);

?>