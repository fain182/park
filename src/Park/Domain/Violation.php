<?php

declare(strict_types=1);

namespace Park\Domain;

class Violation
{
    private string $from;
    private string $to;
    private string $message;

    public function __construct(string $from, string $to, string $message)
    {
        $this->from = $from;
        $this->to = $to;
        $this->message = $message;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getFrom(): string
    {
        return $this->from;
    }

    public function getTo(): string
    {
        return $this->to;
    }
    
    public function __toString(): string
    {
        return $this->message;
    }
}