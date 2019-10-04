<?
namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\DB;

class ShowcodeCommand extends UserCommand {
    protected $name = 'showcode';                      // Your command's name
    protected $description = 'show activation code'; // Your command description
    protected $usage = '/showcode';                    // Usage of your command
    protected $version = '1.0.0';                  // Version of your command

    public function execute() {
        $telegram = $this->getTelegram();
        $chat_id = $telegram->getChatId();
        $caller_member_id = $telegram->getUserId();

        $chat_member = Request::getChatMember(array('chat_id' => $chat_id, 'user_id' => $caller_member_id));
        if ($chat_member->result->status === 'administrator' || $chat_member->result->status === 'creator') {
            $telegram->setActivationCode($chat_id, sha1($chat_id.time()));
            $activation_code = $telegram->getActivationCode($chat_id);
            return Request::sendMessage(array('text' => $activation_code, 'chat_id' => $caller_member_id));
        }
    }
}