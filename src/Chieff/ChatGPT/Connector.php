<?php

namespace Chieff\ChatGPT;

class Connector
{

    public bool $status = true;
    public string $message = "";

    public array $messages = [];

    public function __construct()
    {
        $this->getStatusSession();
        $this->getMessagesSession();
    }

    protected function setStatus($status = true, $message = "", $setSession = false): void
    {
        $this->status = $status;
        $this->message = $message;
        if ($setSession)
            $this->setStatusSession($status, $message);
    }

    protected function setStatusSession($status, $message): void
    {
        $_SESSION["status"] = $status;
        $_SESSION["message"] = $message;
    }

    protected function getStatusSession(): void
    {
        if (isset($_SESSION["status"]))
            $this->status = $_SESSION["status"];
        if (isset($_SESSION["message"]))
            $this->message = $_SESSION["message"];
    }

    public function clearStatusSession(): void
    {
        $this->setStatus(true, "", true);
    }

    protected function getMessagesSession(): void
    {
        if (isset($_SESSION["messages"]) && $_SESSION["messages"])
            $this->messages = $_SESSION["messages"];
    }

    protected function setMessagesSession($messages = []): void
    {
        if (!$messages)
            $messages = $this->messages;
        $_SESSION["messages"] = $messages;
    }

    protected function validateField($field, $checkLength = true, $maxLength = 200) : bool|string
    {
        $field = htmlspecialchars(strip_tags($field));
        if (!$field || ($checkLength && (mb_strlen($field) > $maxLength)))
            return false;
        return $field;
    }

}