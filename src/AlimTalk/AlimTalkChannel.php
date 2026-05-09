<?php

namespace Seungmun\Sens\AlimTalk;

use Illuminate\Notifications\Notification;
use Seungmun\Sens\Exceptions\SensException;

class AlimTalkChannel
{
    /**
     * Create a new SENS alimtalk channel instance.
     */
    public function __construct(protected AlimTalk $alimtalk) {}

    /**
     * Send the specified SENS notification.
     *
     * @param  mixed  $notifiable
     *
     * @throws SensException
     */
    public function send($notifiable, Notification $notification): void
    {
        /** @var AlimTalkMessage $message */
        $message = $notification->{'toAlimTalk'}($notifiable);

        $this->alimtalk->send($message->toArray());
    }
}
