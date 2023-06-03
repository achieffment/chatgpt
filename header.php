        <header class="container header">
            <form action="/?clear_chat" method="post"><button class="button header__button-clear">Clear chat</button></form>
            <? if (!$chatGPT->status): ?><p class="header__errors"><?= $chatGPT->message ?></p><? endif; ?>
        </header>