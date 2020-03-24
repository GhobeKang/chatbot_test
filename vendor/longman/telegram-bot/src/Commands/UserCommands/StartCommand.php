<?php
/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

/**
 * Start command
 */
class StartCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'start';

    /**
     * @var string
     */
    protected $description = 'Start command';

    /**
     * @var string
     */
    protected $usage = '/start';

    /**
     * @var string
     */
    protected $version = '1.1.0';

    /**
     * Command execute method
     *
     * @return ServerResponse
     * @throws TelegramException
     */
    public function execute()
    {
        $telegram = $this->getTelegram();
        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();
        $member_name = $message->getFrom()->getFirstName();

        $msg_content = "
Hi $member_name! Welcome to AQOOM chatbot, your new sidekick for Telegram. 

AQOOM chatbot will help you to manage your Telegram groups easier and more efficient. Analytics, CRM, Scheduled Messages, Anti-Spam, Reports, Message Logs, you name it, we may have it.
        
1. Add @aqoom_bot to your group and make it as an administrator.
        
2. You can proceed to your console/dashboard by logging in here. (Links to www.aqoom.chat)
        
3. You can now configure your filters, functions,  and settings for your group~
        
We are currently in BETA version and we will appreciate if you could give us some feedback! If you cannot login or having any problems, feel free to contact us at info@aqoom.com
";

        $inline_keyboard = [
            'inline_keyboard' => [
                [
                    [
                        'text' => 'Visit AQOOM Website',
                        'url' => 'https://aqoom.chat'
                    ]
                ],[
                    [
                        'text' => 'Add to group',
                        'url' => 'https://t.me/aqoom_bot?startgroup=hbase'
                    ]
                ]
            ]
        ];
        
        $dataset = array(
            'chat_id' => $chat_id,
            'text' => $msg_content,
            'reply_markup' => json_encode($inline_keyboard) 
        );
        Request::sendMessage($dataset);
    }
}
