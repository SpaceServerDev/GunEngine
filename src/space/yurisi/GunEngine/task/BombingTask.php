<?php
declare(strict_types=1);

namespace space\yurisi\GunEngine\task;

use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use space\yurisi\GunEngine\Main;

class BombingTask extends Task {

    public function __construct(private Player $player,
                                private BombingEvent $event,
                                private Main $main,) {
    }

    public function onRun() :void{
        for ($i = 0; $i < 2; $i++) {
            $randx = mt_rand(-20, 20);
            $randz = mt_rand(-20, 20);
            $this->main->getScheduler()->scheduleRepeatingTask(
                new EventGenerator(
                    $this->event->dropBom(
                        $this->player->getPosition()->getX() + $randx, $this->player->getPosition()->getY(), $this->player->getPosition()->getZ() + $randz, $this->player->getWorld()
                    )
                ), 1
            );
        }

        $this->getHandler()->cancel();
    }

    public function onCancel() :void{
        if($this->event->isTask()) {
            $this->main->getScheduler()->scheduleDelayedTask(new BombingTask($this->player, $this->event, $this->main), 10);
            $this->event->addTask();
        }

    }


}