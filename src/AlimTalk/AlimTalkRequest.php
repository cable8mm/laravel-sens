<?php

namespace Seungmun\Sens\AlimTalk;

use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class AlimTalkRequest
{
    public string $plusFriendId = '';

    public string $templateCode = '';

    /** @var array|AlimTalkMessage[] */
    public array $messages = [];

    public string $reserveTime = '';

    public string $reserveTimeZone = '';

    public string $scheduleCode = '';

    public function __construct(array $params)
    {
        $this->mappingParams($params);
    }

    protected function mappingParams(array $params): void
    {
        $attributes = ['plusFriendId', 'templateCode', 'messages', 'reserveTime', 'reserveTimeZone', 'scheduleCode'];

        foreach ($attributes as $attribute) {
            $val = Arr::get($params, $attribute);

            if ($val) {
                $this->{$attribute} = $val;
            }
        }
    }

    public function validator(): ValidatorContract
    {
        return Validator::make($this->toArray(), [
            'plusFriendId' => 'required',
            'templateCode' => 'required',
            'messages' => 'required|array',
            'reserveTime' => 'nullable',
            'reserveTimeZone' => 'nullable',
            'scheduleCode' => 'nullable',
        ]);
    }

    /**
     * @return $this
     */
    public function addMessage(AlimTalkMessage $message): static
    {
        $this->messages[] = $message;

        return $this;
    }

    public function toArray(): array
    {
        $buffer = [
            'plusFriendId' => $this->plusFriendId,
            'templateCode' => $this->templateCode,
            'messages' => array_map(
                function (AlimTalkMessage $message): array {
                    return $message->toArray();
                },
                $this->messages
            ),
        ];

        if ($this->reserveTime) {
            $buffer['reserveTime'] = $this->reserveTime;
        }

        if ($this->reserveTimeZone) {
            $buffer['reserveTimeZone'] = $this->reserveTimeZone;
        }

        if ($this->scheduleCode) {
            $buffer['scheduleCode'] = $this->scheduleCode;
        }

        return $buffer;
    }
}
