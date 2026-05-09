<?php

namespace Seungmun\Sens\Sms;

use Illuminate\Notifications\Notification;
use Seungmun\Sens\Exceptions\SensException;

class SmsChannel
{
    /**
     * SENS instance implements.
     *
     * @var Sms
     */
    protected $sms;

    /**
     * Create a new SENS sms channel instance.
     */
    public function __construct(Sms $sens)
    {
        $this->sms = $sens;
    }

    /**
     * Send the specified SENS notification.
     *
     * @param  mixed  $notifiable
     * @return void
     *
     * @throws SensException
     */
    public function send($notifiable, Notification $notification)
    {
        /** @var SmsMessage $message */
        $message = $notification->{'toSms'}($notifiable);

        $this->sms->send($message->toArray());
    }
}
