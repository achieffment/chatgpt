<?php

namespace Chieff\ChatGPT;

use Chieff\ChatGPT\Connector;

class ChatGPT extends Connector {

    private string $token;
    private string $orgId;
    private string $model;

    public function __construct($token, $orgId = "", $model = "")
    {
        parent::__construct();
        if (!$token)
            throw new Exception("You didn't typed a token!");
        $this->token = $token;
        $this->orgId = $orgId;
        $this->model = $model;
        if (!$this->receiver())
            $this->clearChat();
    }

    public function testConnection()
    {
        return $this->sendRequest("chat/completions", [
            "messages" => [
                [
                    "role" => "user",
                    "content" => "Say this is a test!"
                ]
            ],
            "temperature" => 0.7
        ]);
    }

    private function clearChat() : void
    {
        if (isset($_SESSION["messages"]) && $_SESSION["messages"]) {
            $this->messages = [];
            $_SESSION["messages"] = [];
        }
    }

    private function validateMessage($message): bool
    {
        if (!$message) {
            $this->setStatus(false, "Message is empty!");
            return false;
        }
        if (!is_array($message)) {
            $this->setStatus(false, "Message is not an array!");
            return false;
        }
        $keysCount = count($message);
        if (($keysCount < 2) || ($keysCount > 3)) {
            $this->setStatus(false, "Messages keys must be 2 or 3! (role, content, name (optional))");
            return false;
        }
        $roles = ["system", "user", "assistant"];
        if (!isset($message["role"]) || (!$role = $this->validateField($message["role"], true,20))) {
            $this->setStatus(false, "Message doesn't contain a role key or it's invalid, max length - 20!");
            return false;
        } else if (!in_array($role, $roles)) {
            $this->setStatus(false, "Message's role key must be system, user or assistant!");
            return false;
        }
        if (!isset($message["content"]) || (!$content = $this->validateField($message["content"], true, 1000))) {
            $this->setStatus(false, "Message doesn't contain a content key or it's invalid, max length - 300!");
            return false;
        }
        $keysCounter = 2;
        if (isset($message["name"]) && (!$name = $this->validateField($message["name"], true,50))) {
            $this->setStatus(false, "Message's name key is invalid, max length - 50!");
            return false;
        } else
            $keysCounter++;
        if (($keysCount > 2) && ($keysCounter == 2)) {
            $this->setStatus(false, "Message contains not allowed keys!");
            return false;
        }
        return true;
    }

    private function validateMessages($messages): bool
    {
        if (!is_array($messages)) {
            $this->setStatus(false, "Messages is not an array!");
            return false;
        }
        foreach ($messages as $message) {
            if (!$this->validateMessage($message))
                return false;
        }
        return true;
    }

    private function validateTemperature($temperature) : bool
    {
        if (!$temperature = $this->validateField($temperature, true,5)) {
            $this->setStatus(false, "Temperature is empty or invalid, max length - 5!");
            return false;
        }
        if (!is_numeric($temperature) || ($temperature < 0.1) || ($temperature > 2.0)) {
            $this->setStatus(false, "Temperature must be numeric and more than 0.1 and less than 2");
            return false;
        }
        return true;
    }

    public function listModels()
    {
        return $this->sendRequest("models");
    }

    public function retrieveModel($model)
    {
        if (!$model = $this->validateField($model)) {
            $this->setStatus(false, "Model name is empty or is invalid, max length - 200!");
            return false;
        }
        return $this->sendRequest("models/" . $model);
    }

    public function createCompletion($prompt, $temperature = "", $model = "")
    {
        if (!$prompt = $this->validateField($prompt, true,300)) {
            $this->setStatus(false, "Prompt is empty or invalid, max length - 300!");
            return false;
        }
        $data = ["prompt" => $prompt];
        if ($temperature && (!$temperature = $this->validateTemperature($temperature)))
            return false;
        else if ($temperature)
            $data["temperature"] = $temperature;
        if ($model && (!$model = $this->validateField($model))) {
            $this->setStatus(false, "Model name is invalid, max length - 200!");
            return false;
        } else if ($model)
            $data["model"] = $model;
        else
            $data["model"] = "text-davinci-003";
        return $this->sendRequest("completions", $data);
    }

    public function createChatCompletion($messages, $temperature = "", $model = "")
    {
        if (!$this->validateMessages($messages))
            return false;
        $data = ["messages" => $messages];
        if ($temperature)
            $data["temperature"] = $temperature;
        if ($model)
            $data["model"] = $model;
        return $this->sendRequest("chat/completions", $data);
    }

    public function sendRequest($command, array $data = [])
    {
        $headers = ['Authorization: Bearer ' . $this->token];
        if ($this->orgId)
            $headers[] = 'OpenAI-Organization: ' . $this->orgId;
        if ($data) {
            if (!isset($data["model"]) || !$data["model"])
                $data["model"] = "gpt-3.5-turbo";
            $data = json_encode($data);
            $headers[] = "Content-Type: application/json";
            $headers[] = "Content-Length: " . mb_strlen($data);
        }
        $ch = curl_init('https://api.openai.com/v1/' . $command);
        if ($data) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, true);
    }

    private function receiver()
    {
        if (isset($_REQUEST["clear_chat"])) {
            $this->clearChat();
            $this->clearStatusSession();
        } else if (
            isset($_REQUEST["send_message"]) &&
            isset($_POST["message"]) &&
            $_POST["message"]
        ) {
            $message = [];
            $message["content"] = $this->validateField($_POST["message"], false);
            if (isset($_POST["role"]) && $_POST["role"])
                $message["role"] = $this->validateField($_POST["role"], false);
            else
                $message["role"] = "user";
            if (isset($_POST["name"]) && $_POST["name"])
                $message["name"] = $this->validateField($_POST["name"], false);
            if (!$this->validateMessage($message))
                return false;
            $temperature = "";
            if (isset($_POST["temperature"]) && $_POST["temperature"] && (!$temperature = $this->validateTemperature($_POST["temperature"])))
                return false;
            $model = "";
            if (isset($_POST["model"]) && $_POST["model"] && (!$model = $this->validateField($_POST["model"]))) {
                $this->setStatus(false, "Model name is invalid, max length - 200!");
                return false;
            }
            $this->messages[] = $message;
            unset($message);
            $this->setMessagesSession();
            if ($answer = $this->createChatCompletion($this->messages, $temperature, $model)) {
                if (!$this->handleAnswer($answer))
                    return false;
            } else
                return false;
        }
        return true;
    }

    private function handleAnswer($answer)
    {
        $status = true;
        if (!is_array($answer)) {
            $this->setStatus(false, "Something went wrong, answer is invalid!");
            return false;
        }
        if (
            isset($answer["error"]) &&
            isset($answer["error"]["type"]) &&
            isset($answer["error"]["message"])
        ) {
            $this->setStatus(false, "Error request: " . $answer["error"]["type"] . " : " . $answer["error"]["message"]);
            return false;
        }
        $message = ["role" => "assistant"];
        if (
            isset($answer["choices"]) && $answer["choices"] &&
            isset($answer["choices"][0]) && $answer["choices"][0] &&
            isset($answer["choices"][0]["message"]) && $answer["choices"][0]["message"] &&
            isset($answer["choices"][0]["message"]["content"]) && $answer["choices"][0]["message"]["content"]
        ) {
            $message["content"] = $answer["choices"][0]["message"]["content"];
            $this->messages[] = $message;
            $this->setMessagesSession();
        } else {
            $this->setStatus(false, "Something went wrong, answer is invalid!");
            return false;
        }
        return true;
    }

}