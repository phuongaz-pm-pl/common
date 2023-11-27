<?php

declare(strict_types=1);

namespace faz\common\form;

use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Label;
use dktapps\pmforms\ModalForm;
use Generator;
use pocketmine\player\Player;
use SOFe\AwaitGenerator\Await;

class FastForm {

    public static function simpleNotice(Player $player, string $message, ?\Closure $onSubmit = null) {
        $player->sendForm(new CustomForm(
            "Notice",
            [
                new Label("message", $message)
            ],
            function (Player $player, null|CustomFormResponse $response) use ($onSubmit)  : void{
                if(!is_null($onSubmit)) {
                    $onSubmit();
                }
            }
        ));
    }

    public static function question(Player $player, string $title, string $message, string $yes, string $no, ?\Closure $onA = null, ?\Closure $onClose = null): void {
        $player->sendForm(new ModalForm(
            $title,
            $message,
            function (Player $player, bool $response) use ($onA, $onClose) : void {
                if($response) {
                    $onA($response);
                } else {
                    $onClose();
                }
            },
            $yes,
            $no
        ));
    }
}