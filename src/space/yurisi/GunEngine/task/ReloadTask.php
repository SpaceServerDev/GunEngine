<?php
declare(strict_types=1);

namespace space\yurisi\GunEngine\task;

use pocketmine\world\World;
use pocketmine\network\mcpe\protocol\TextPacket;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use space\yurisi\GunEngine\EventListener;
use space\yurisi\GunEngine\Main;

class ReloadTask extends Task {

    public function __construct(
        private Player $player,
        private EventListener $event,
        private string $gun,
        private string $serial,
        private Main $main,
        private bool $reload=true,
    ) {
    }

    /**
     * Actions to execute when run
     *
     * @return void
     */
    public function onRun():void {
        $this->main->getGunManager()->getGunData($this->gun, $this->serial)->endCoolDown();
        if ($this->player->getWorld() instanceof World) {
            if ($this->reload) {
                $pk = new TextPacket();
                $pk->type = 4;
                $pk->message = $this->gun . " Reload Complete";
                $this->player->getNetworkSession()->sendDataPacket($pk);
                $this->event->sound("music.machinegun-magazine-set1", $this->player->getPosition()->getFloorX(), $this->player->getPosition()->getFloorY(), $this->player->getPosition()->getFloorZ(), $this->player->getWorld());
            }
        }
        $this->getHandler()->cancel();
    }
}
