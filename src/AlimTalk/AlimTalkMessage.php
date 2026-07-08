<?php

namespace Seungmun\Sens\AlimTalk;

class AlimTalkMessage
{
    public string $countryCode = '82';

    public string $to = '';

    public string $content = '';

    public array $buttons = [];

    protected ?string $reserveTime = null;

    protected ?string $reserveTimeZone = null;

    protected ?string $scheduleCode = null;

    protected string $templateCode = '';

    protected ?string $plusFriendId;

    /**
     * Create a new AlimTalkMessage instance.
     */
    public function __construct(?string $friendId = null)
    {
        $this->plusFriendId = $friendId ? $friendId : config('laravel-sens.plus_friend_id');
    }

    /**
     * @return $this
     */
    public function countryCode(string $countryCode): static
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * @return $this
     */
    public function to(string $to): static
    {
        $this->to = $to;

        return $this;
    }

    /**
     * @return $this
     */
    public function content(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return $this
     */
    public function addButton(array $button): static
    {
        $this->buttons[] = $button;

        return $this;
    }

    /**
     * @return AlimTalkMessage
     */
    public function setReserved(string $reserveTime, string $reserveTimeZone = 'Asia/Seoul'): static
    {
        $this->reserveTime = $reserveTime;
        $this->reserveTimeZone = $reserveTimeZone;

        return $this;
    }

    /**
     * @return $this
     */
    public function setSchedule(string $code): static
    {
        $this->scheduleCode = $code;

        return $this;
    }

    /**
     * @return $this
     */
    public function plusFriendId(string $id): static
    {
        $this->plusFriendId = $id;

        return $this;
    }

    /**
     * @return $this
     */
    public function templateCode(string $code): static
    {
        if (empty($code)) {
            throw new \InvalidArgumentException('Template code cannot be empty.');
        }

        $this->templateCode = $code;

        return $this;
    }

    public function toArray(): array
    {
        $buffer = [
            'plusFriendId' => $this->plusFriendId,
            'templateCode' => $this->templateCode,
            'scheduleCode' => $this->scheduleCode,
        ];

        if ($this->reserveTime) {
            $buffer['reserveTime'] = $this->reserveTime;
        }

        if ($this->reserveTimeZone) {
            $buffer['reserveTimeZone'] = $this->reserveTimeZone;
        }

        $message = [
            'countryCode' => $this->countryCode,
            'to' => $this->to,
            'content' => $this->content,
        ];

        if (count($this->buttons)) {
            $message['buttons'] = $this->buttons;
        }

        $buffer['messages'][] = $message;

        return $buffer;
    }
}
