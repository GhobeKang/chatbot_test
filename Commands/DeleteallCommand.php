<?
namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\AdminCommand;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\DB;

class DeleteallCommand extends AdminCommand {
    protected $name = 'deleteall';                      // Your command's name
    protected $description = 'delete all word of forbiddened in a chat room.'; // Your command description
    protected $usage = '/deleteall';                    // Usage of your command
    protected $version = '1.0.0';                  // Version of your command

    public function execute() {
        if($messages = DB::selectMessages()) {
            $telegram = $this->getTelegram();
            $forbidden_lists = $telegram->getForbiddenLists();
            
            foreach($messages as $msg) {
                $text = $msg['text'];

                foreach($forbidden_lists as $word) {
                    if (strpos($text, $word) !== false) {
                        $data_set = [
                            'chat_id' => $msg['chat_id'],
                            'message_id' => $msg['id']
                        ];
                        $result = Request::deleteMessage($data_set);
                        if ($result->isOk()) {
                            // send announce message
                            return true;
                        }
                    }
                }
            }
        };
    }
}