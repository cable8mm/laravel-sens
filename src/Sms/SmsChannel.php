<?php

namespace Seungmun\Sens\Sms;

use Illuminate\Notifications\Notification;
use Seungmun\Sens\Exceptions\SensException;

class SmsChannel
{
    /**
     * Create a new SENS sms channel instance.
     */
    public function __construct(protected Sms $sms) {}

    /**
     * Send the specified SENS notification.
     *
     * @param  mixed  $notifiable
     *
     * @throws SensException
     */
    public function send($notifiable, Notification $notification): void
    {
        /** @var SmsMessage $message */
        $message = $notification->{'toSms'}($notifiable);

        $this->sms->send($message->toArray());
    }
}
