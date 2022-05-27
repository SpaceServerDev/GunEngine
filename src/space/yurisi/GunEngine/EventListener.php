<?php
declare(strict_types=1);

namespace space\yurisi\GunEngine;

use pocketmine\world\Explosion;
use pocketmine\world\World;
use pocketmine\world\particle\SmokeParticle;
use pocketmine\world\Position;
use pocketmine\Server;
use pocketmine\player\Player;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\world\particle\FlameParticle;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use space\yurisi\GunEngine\guns\Gun;
use space\yurisi\GunEngine\task\ShootTask;
use space\yurisi\GunEngine\task\ReloadTask;
use space\yurisi\GunEngine\task\EventGenerator;

class EventListener implements Listener {
    public function __construct(private Main $main) {
    }

    public function onTouch(PlayerInteractEvent $event) {
        $player = $event->getPlayer();
        $manager = $this->main->getGunManager();
        if ($player->getInventory()->getItemInHand()->getNamedTag()->offsetExists("gun")) {
            $gun = $player->getInventory()->getItemInHand()->getNamedTag()->getString("gun");
            $serial = $player->getInventory()->getItemInHand()->getNamedTag()->getString("serial");
            $auto = $player->getInventory()->getItemInHand()->getNamedTag()->getString("fullauto");
            $gun_data = $manager->getGunData($gun, $serial);

            if (!$gun_data->isShootNow()) {
                if ($gun_data->getAmmo() > 0) {
                    if ($player->isSneaking()) {
                        if ($gun_data->getAmmo() !== $gun_data->getMaxAmmo()) {
                            $this->reloadEvent($player, $gun_data, $gun, $serial);
                            $this->sound("music.machinegun-magazine-remove1", $player->getPosition()->getFloorX(), $player->getPosition()->getFloorY(), $player->getPosition()->getFloorZ(), $player->getWorld());
                            return;
                        }
                    }
                }
            }

            /*full auto*/
            if ($auto == "yes") {
                if (!$gun_data->getCoolDown()) {
                    if (!$gun_data->isShootNow()) {
                        if ($gun_data->getAmmo() > 0) {
                            $task = new ShootTask($player, $gun_data, $this);
                            $this->main->getScheduler()->scheduleDelayedRepeatingTask($task, (int)$gun_data->getDelayTick(), (int)$gun_data->getPeriodTick());
                            $gun_data->startShoot($task->getHandler());
                            return;
                        } else {
                            $this->reloadEvent($player, $gun_data, $gun, $serial);
                            $this->sound("music.machinegun-magazine-remove1", $player->getPosition()->getFloorX(), $player->getPosition()->getFloorY(), $player->getPosition()->getFloorZ(), $player->getWorld());
                            return;
                        }
                    } else {
                        $gun_data->getTaskId()->cancel();
                        $gun_data->endShoot();
                        $this->sound("music.cartridge1", $player->getFloorX(), $player->getFloorY(), $player->getFloorZ(), $player->getLevel());
                        return;
                    }
                }
            }

            /*semi auto*/
            if ($auto == "sniper" or $auto == "rocket") {
                if (!$gun_data->getCoolDown()) {
                    if (!$gun_data->isShootNow()) {
                        if ($gun_data->getAmmo() > 0) {
                            $gun_data->removeAmmo();
                            if ($auto == "sniper") {
                                $this->noTaskShootEvent($player, $gun_data);
                                $this->sound("music.largerifle-firing1", $player->getFloorX(), $player->getFloorY(), $player->getFloorZ(), $player->getLevel());
                            } else {
                                main::getMain()->getScheduler()->scheduleRepeatingTask(new EventGenerater($this->RocketShootEvent($player, $gun_data)), 0.2);
                                $this->sound("music.missile1", $player->getFloorX(), $player->getFloorY(), $player->getFloorZ(), $player->getLevel());
                            }
                            if ($player->getWorld()->getFolderName() !== "world") {
                                $motion = $player->getDirectionVector()->multiply(-$gun_data->getRecoil());
                                $player->setMotion($motion);

                                $pk = new MovePlayerPacket();
                                $pk->entityRuntimeId = $player->getId();
                                $pk->position = $player->getPosition();
                                $pk->yaw = $player->getLocation()->getYaw();
                                $pk->pitch = $player->getLocation()->getPitch() - 2;
                                $pk->headYaw = $player->getLocation()->getYaw() - 2;
                                $pk->mode = MovePlayerPacket::MODE_PITCH;
                                $pk->onGround = $player->isOnGround();
                                $pk->entityRuntimeId = $player->getId();
                                $player->getNetworkSession()->sendDataPacket($pk);
                                $player->resetFallDistance();
                                $player->setForceMovementUpdate();
                            }
                            $ammo = "";
                            $now = $gun_data->getAmmo();
                            for ($i = 0; $i < $now; $i++) {
                                $ammo = $ammo . "||";
                            }
                            for ($i = 0; $i < $gun_data->getMaxAmmo() - $now; $i++) {
                                $ammo = $ammo . " ";
                            }
                            $player->sendPopup("§c" . $ammo . "§e({$now})");
                            $gun_data->startCoolDown();
                            $this->main->getScheduler()->scheduleDelayedTask(new ReloadTask($player, $this, $gun, $serial, $this->main,false), $gun_data->getCoolDownTick());
                            if ($auto == "sniper") {
                                $this->sound("music.sniperrifle-boltaction1", $player->getFloorX(), $player->getFloorY(), $player->getFloorZ(), $player->getLevel());
                            }

                            return;
                        } else {
                            $this->reloadEvent($player, $gun_data, $gun, $serial);
                            return;
                        }
                    }
                }
            }
        }
    }

    private function reloadEvent(Player $player, Gun $gun_data, string $gun, string $serial) {
        $player->sendPopup("Reloaded");
        $gun_data->reload();
        $gun_data->startCoolDown();
        $this->main->getScheduler()->scheduleDelayedTask(new ReloadTask($player, $this, $gun, $serial, $this->main), $gun_data->getReloadTick());
    }

    public function shootEvent(Player $entity, Gun $gundata): \Generator {
        $particle = new FlameParticle();
        $particle->encode(new Vector3($entity->getPosition()->x, $entity->getPosition()->y + 1.5, $entity->getPosition()->z));

        $increase = $entity->getDirectionVector()->normalize();
        for ($i = 0; $i < $gundata->getDistance(); $i++) {
            yield;
            $pos = $particle->add($increase);
            if ($entity->level instanceof Level) {
                if (!$entity->level->getBlock($pos)->canBeFlowedInto()) {
                    foreach ($entity->level->getPlayers() as $player) {
                        if ($player->distance($pos) < 8.0 && $entity !== $player) {
                            $this->playerSound("music.bullets-bounce1", $player, 0.6);
                        }
                    }
                    break;
                }
                $particle->setComponents($pos->x, $pos->y, $pos->z);
                $entity->level->addParticle($particle);
                foreach ($entity->level->getPlayers() as $player) {
                    if ($player->distance($pos) < 1.5 && $entity !== $player) {
                        $event = new EntityDamageByEntityEvent($entity, $player, EntityDamageEvent::CAUSE_PROJECTILE, $gundata->getDamage(), [], $gundata->getKnockBack());
                        $this->sound("music.attack", $entity->getFloorX(), $entity->getFloorY(), $entity->getFloorZ(), $entity->getLevel());
                        $player->attack($event);
                        break 2;
                    }
                }
            }
        }
    }

    public function noTaskShootEvent(Player $entity, Gun $gundata) {
        $particle = new FlameParticle(new Vector3($entity->x, $entity->y + 1.5, $entity->z));
        $particle->setComponents($entity->x, $entity->y + 1.5, $entity->z);

        $increase = $entity->getDirectionVector()->normalize();
        for ($i = 0; $i < $gundata->getDistance(); $i++) {
            $pos = $particle->add($increase);
            if ($entity->level instanceof Level) {
                if (!$entity->level->getBlock($pos)->canBeFlowedInto()) {
                    foreach ($entity->level->getPlayers() as $player) {
                        if ($player->distance($pos) < 8.0 && $entity !== $player) {
                            $this->playerSound("music.bullets-bounce1", $player, 0.6);
                        }
                    }
                    break;
                }
                $particle->setComponents($pos->x, $pos->y, $pos->z);
                $entity->level->addParticle($particle);
                foreach ($entity->level->getPlayers() as $player) {
                    if ($player->distance($pos) < 1.5 && $entity !== $player) {
                        $event = new EntityDamageByEntityEvent($entity, $player, EntityDamageEvent::CAUSE_PROJECTILE, $gundata->getDamage(), [], $gundata->getKnockBack());
                        $this->sound("music.attack", $entity->getFloorX(), $entity->getFloorY(), $entity->getFloorZ(), $entity->getLevel());
                        $player->attack($event);
                        break 2;
                    }
                }
            }
        }
    }

    public function RocketShootEvent(Player $entity, Gun $gundata) {
        $pos = new Vector3($entity->x, $entity->y + 1.5, $entity->z);
        $particle = new SmokeParticle($pos, 1000);
        $particle->setComponents($entity->x, $entity->y + 1.5, $entity->z);

        $increase = $entity->getDirectionVector()->normalize();
        for ($i = 0; $i < $gundata->getDistance(); $i++) {
            yield;
            $pos = $particle->add($increase);
            if (!$entity->level->getBlock($pos)->canBeFlowedInto()) {
                $explosion = new Explosion(new Position($pos->x, $pos->y, $pos->z, $entity->getLevel()), 1);
                $explosion->explodeB();
                $this->sound("music.bomb2", $entity->getFloorX(), $entity->getFloorY(), $entity->getFloorZ(), $entity->getLevel(), 0.4);
                break;
            }
            $particle->setComponents($pos->x, $pos->y, $pos->z);
            $entity->level->addParticle($particle);
            foreach ($entity->level->getPlayers() as $player) {
                if ($player->distance($pos) < 1.5 && $entity !== $player) {
                    $explosion = new Explosion(new Position($pos->x, $pos->y, $pos->z, $entity->getLevel()), 1);
                    $explosion->explodeB();
                    $this->sound("music.bomb2", $entity->getFloorX(), $entity->getFloorY(), $entity->getFloorZ(), $entity->getLevel(), 0.4);
                    break 2;
                }
            }
        }
        $explosion = new Explosion(new Position($pos->x, $pos->y, $pos->z, $entity->getLevel()), 1);
        $explosion->explodeB();
        $this->sound("music.bomb2", $entity->getFloorX(), $entity->getFloorY(), $entity->getFloorZ(), $entity->getLevel(), 0.4);
    }

    public function sound(string $name, $x, $y, $z, World $level, $vol = 0.3) {
        $pk2 = new PlaySoundPacket;
        $pk2->soundName = $name;
        $pk2->x = $x;
        $pk2->y = $y;
        $pk2->z = $z;
        $pk2->volume = $vol;
        $pk2->pitch = 1;
        Server::getInstance()->broadcastPackets($level->getPlayers(), [$pk2]);
    }

    public function playerSound(string $name, Player $player, $vol = 0.3) {
        $pk2 = new PlaySoundPacket;
        $pk2->soundName = $name;
        $pk2->x = $player->x;
        $pk2->y = $player->y;
        $pk2->z = $player->z;
        $pk2->volume = $vol;
        $pk2->pitch = 1;
        $player->dataPacket($pk2);
    }
}