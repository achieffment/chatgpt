<section class="container main">
    <div class="main__messages">
        <?
        if ($chatGPT->messages):
            foreach ($chatGPT->messages as $message):
                ?>
                <div class="main__messages-message">
                    <span class="main__messages-message-role"><?= (isset($message["role"]) && $message["role"]) ? $message["role"] : "???" ?></span>
                    <? if (isset($message["name"])): ?>
                    <span class="main__messages-message-name"><?= ($message["name"]) ? $message["name"] : "???" ?></span>
                    <? endif; ?>
                    <span class="main__messages-message-text"><?= (isset($message["content"]) && $message["content"]) ? str_replace("\n", "<br>", $message["content"]) : "Can not recognize message text..." ?></span>
                </div>
            <? endforeach; ?>
        <? else: ?>
            <p class="main__nomessage">You don't have messages yet...</p>
        <? endif; ?>
        <?
            echo "<pre>";
            print_r($chatGPT->answer);
            echo "</pre>";
        ?>
    </div>
    <div class="main__input-container">
        <form class="main__input-form" action="/?send_message" method="post">
            <textarea class="input main__input-form-text" name="message" placeholder="Type a message..."></textarea>
            <input class="button main__input-form-submit" type="submit" value="Send">
        </form>
    </div>
</section>
<?php
    $chatGPT->clearStatusSession();
?>