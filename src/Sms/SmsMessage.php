<?php

namespace Seungmun\Sens\Sms;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\UploadedFile;
use Seungmun\Sens\Contracts\SensMessage;

class SmsMessage implements SensMessage
{
    public string $type = 'SMS';

    public string $contentType = 'COMM';

    public int $countryCode = 82;

    public ?string $from;

    public ?string $subject = null;

    public string $content = '';

    public array $messages = [];

    public array $files = [];

    /**
     * Create a new SensSmsMessage instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->from = config('services.sens.services.sms.sender');
    }

    /**
     * Set SMS Type (ex: SMS, LMS)
     *
     * @return $this
     */
    public function type(string $type): static
    {
        $this->type = strtoupper($type);

        return $this;
    }

    /**
     * Set SMS Content Type (ex: COMM / AD)
     *
     * @return $this
     */
    public function contentType(string $contentType): static
    {
        $this->contentType = strtoupper($contentType);

        return $this;
    }

    /**
     * Set Country Code.
     *
     * @return $this
     */
    public function countryCode(int $countryCode): static
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * Set Sender's tel number.
     *
     * @return $this
     */
    public function from(string $from): static
    {
        $this->from = str_replace('-', '', $from);

        return $this;
    }

    /**
     * Set title only for LMS.
     *
     * @return $this
     */
    public function subject(string $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Set SMS Contents. (SMS: 80byte, LMS: 2000byte)
     *
     * @return $this
     */
    public function content(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Set Recipient's number.
     *
     * @return $this
     */
    public function to(string $to): static
    {
        $this->messages[] = [
            'to' => str_replace('-', '', $to),
        ];

        return $this;
    }

    /**
     * Add a new file into files for MMS message.
     *
     * @return $this
     *
     * @throws FileNotFoundException
     */
    public function file(string $name, mixed $file): static
    {
        $body = null;

        if ($file instanceof UploadedFile) {
            /** @var UploadedFile $file */
            $body = base64_encode($file->get());
        } elseif (is_string($file)) {
            $body = base64_encode(file_get_contents($file));
        } else {
            throw new FileNotFoundException;
        }

        $this->files[] = [
            'name' => $name,
            'body' => $body,
        ];

        return $this;
    }

    /**
     * Serialize to Array.
     */
    public function toArray(): array
    {
        $resource = [
            'type' => $this->type,
            'contentType' => $this->contentType,
            'countryCode' => strval($this->countryCode),
            'from' => $this->from,
        ];

        if ($this->subject !== null) {
            $resource['subject'] = $this->subject;
        }

        $resource['content'] = $this->content;
        $resource['messages'] = $this->messages;

        if (! empty($this->files)) {
            $resource['files'] = $this->files;
        }

        return $resource;
    }
}
