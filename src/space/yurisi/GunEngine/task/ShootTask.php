<?php
declare(strict_types=1);

namespace space\yurisi\GunEngine\task;

use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use space\yurisi\GunEngine\EventListener;
use space\yurisi\GunEngine\guns\Gun;
use space\yurisi\GunEngine\Main;

class ShootTask extends Task {

    private string $space = "";

    public function __construct(
        private Player        $player,
        private Gun           $gun,
        private EventListener $event,
        private Main $main
    ) {
    }

    /**
     * Actions to execute when run
     *
     * @return void
     */
    public function onRun(): void {
        if ((!$this->player->getInventory()->getItemInHand()->getNamedTag()->offsetExists("gun")) ||
            $this->gun->getAmmo() <= 0 ||
            $this->player->getInventory()->getItemInHand()->getNamedTag()->getString("gun") !== $this->gun->getName()
        ) {
            $this->getHandler()->cancel();
            $this->gun->endShoot();
            $this->event->sound("music.cartridge1", $this->player->getPosition()->getFloorX(), $this->player->getPosition()->getFloorY(), $this->player->getPosition()->getFloorZ(), $this->player->getWorld());
            return;
        }
        $this->gun->removeAmmo();
        $this->event->sound("music.sniperrifle--firing1", $this->player->getFloorX(), $this->player->getFloorY(), $this->player->getFloorZ(), $this->player->getLevel());
        $this->main->getScheduler()->scheduleRepeatingTask(new EventGenerator($this->event->shoot($this->player, $this->gun)), 1);

        $motion = $this->player->getDirectionVector()->multiply(-$this->gun->getRecoil());
        $this->player->setMotion($motion);

        $pk = new MovePlayerPacket();
        $pk->entityRuntimeId = $this->player->getId();
        $pk->position = $this->player->getPosition();
        $pk->yaw = $this->player->getYaw();
        $pk->pitch = $this->player->getPitch() - 2;
        $pk->headYaw = $this->player->getYaw() - 2;
        $pk->mode = MovePlayerPacket::MODE_PITCH;
        $pk->onGround = $this->player->isOnGround();
        $pk->entityRuntimeId = $this->player->getId();
        $this->player->sendDataPacket($pk);
        $this->player->resetFallDistance();
        $this->player->setForceMovementUpdate();
        $ammo = "";
        $now = $this->gun->getAmmo();
        for ($i = 0; $i < $now; $i++) {
            $ammo = $ammo . "||";
        }
        for ($i = 0; $i < $this->gun->getMaxAmmo() - $now; $i++) {
            $ammo = $ammo . " ";
        }
        $this->player->sendPopup("§c" . $ammo . "§e({$now})");


    }
}