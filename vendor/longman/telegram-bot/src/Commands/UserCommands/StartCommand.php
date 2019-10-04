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
        $user_id = $message->getFrom()->getId();

        $startMsg = $telegram->getStartMessage($chat_id);
        if ($startMsg) {
            $startMsg = $startMsg[0];
            if ($startMsg['response_type'] === 'txt') {
                $dataset = array(
                    'chat_id' => $chat_id,
                    'text' => $startMsg['content_txt']
                );
                Request::sendMessage($dataset);
            } else if ($startMsg['response_type'] === 'img') {
                $dataset = array(
                    'chat_id' => $chat_id,
                    'photo' => $startMsg['content_img']
                );
                Request::sendPhoto($dataset);
            }
        }
    }
}
