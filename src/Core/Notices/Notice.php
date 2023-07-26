<?php

namespace LEXO\AcfIF\Core\Notices;

class Notice
{
    private static array $allowed_notice_types = [
        'info',
        'success',
        'error',
        'warning',
    ];

    private string $message;
    private string $type = 'info';
    private bool $dismissible = true;

    public function message(string $message)
    {
        $this->message = $message;

        return $this;
    }

    public function dismissible(bool $dismissible)
    {
        $this->dismissible = $dismissible;

        return $this;
    }

    public function type(string $type)
    {
        $this->type = in_array($type, self::$allowed_notice_types) ? $type : 'info';

        return $this;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function isDismissible()
    {
        return $this->dismissible;
    }

    public function gettype()
    {
        return $this->type;
    }
}
