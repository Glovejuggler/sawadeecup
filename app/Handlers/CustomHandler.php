<?php

namespace App\Handlers;

use Casperlaitw\LaravelFbMessenger\Contracts\BaseHandler;
use Casperlaitw\LaravelFbMessenger\Messages\ReceiveMessage;
use Casperlaitw\LaravelFbMessenger\Messages\ButtonTemplate;

class CustomHandler extends BaseHandler
{
    public function handle(ReceiveMessage $message)
    {
        $button = new ButtonTemplate($message->getSender(), 'Default text');
        $button
            ->setText('Choose')
            ->addPostBackButton('First Bbutton')
            ->addPostBackButton('Second Button')
            ->addPostBackButton('Third button');
        $this->send($button);
    }
}