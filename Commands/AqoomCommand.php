<?
namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\DB;

class AqoomCommand extends UserCommand {
    protected $name = 'aqoom';                      // Your command's name
    protected $description = 'show activation code'; // Your command description
    protected $usage = '/aqoom';                    // Usage of your command
    protected $version = '1.0.0';                  // Version of your command

    public function execute() {
        $telegram = $this->getTelegram();
        $chat_id = $telegram->getChatId();
        $caller_member_id = $telegram->getUserId();

        $chat_member = Request::getChatMember(array('chat_id' => $chat_id, 'user_id' => $caller_member_id));
        if ($chat_member->result->status === 'administrator' || $chat_member->result->status === 'creator') {
            $telegram->setActivationCode($chat_id, sha1($chat_id.time()));
            $activation_code = $telegram->getActivationCode($chat_id);
            $room_id = $telegram->getChatId();
            $base_url = 'https://aqoom.chat/';
            $full_url = $base_url. '?id=' . $room_id . '&code=' .  $activation_code;
            $this->delMsg($telegram, 'text');

            return Request::sendMessage(array('text' => $full_url, 'chat_id' => $caller_member_id, 'parse_mode' => 'html', 'disable_web_page_preview' => false));
        }
    }

    private function delMsg($telegram, $type, $img='') {
        $params = $telegram->getEditParams();

        $data = [
            'chat_id' => (string)$params['chat_id'],
            'message_id' => (string)$params['message_id']
        ];
        $result = Request::deleteMessage($data);
        if ($result->isOk()) {
            // send announce message
            $telegram->delMessage($data['message_id'], $data['chat_id'], $type, $img);
            return true;
        }
    }
}