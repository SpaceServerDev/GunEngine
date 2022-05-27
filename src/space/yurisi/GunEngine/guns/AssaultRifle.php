<?php
declare(strict_types=1);

namespace space\yurisi\GunEngine\guns;

use pocketmine\scheduler\TaskHandler;

class AssaultRifle implements Gun {

    const MAX_AMMO = 30;

    private bool $CoolDown = false;

    private bool $shoot_now = false;

    private int $ammo = self::MAX_AMMO;

    private ?TaskHandler $handler;

    public function getName(): string {
        return "AK47";
    }

    public function getAmmo(): int {
        return $this->ammo;
    }

    public function removeAmmo() {
        $this->ammo--;
    }

    public function reload() {
        $this->ammo = self::MAX_AMMO;
    }

    public function getMaxAmmo(): int {
        return self::MAX_AMMO;
    }

    public function startCoolDown() {
        $this->CoolDown = true;
    }

    public function endCoolDown() {
        $this->CoolDown = false;
    }

    public function getCoolDown(): bool {
        return $this->CoolDown;
    }

    public function getReloadTick(): int {
        return 50;
    }

    public function startShoot(TaskHandler $handler) {
        $this->shoot_now = true;
        $this->handler = $handler;
    }

    public function endShoot() {
        $this->shoot_now = false;
        $this->handler = null;
    }

    public function getTaskId(): ?TaskHandler{
        if (!$this->isShootNow()) {
            return null;
        }
        return $this->handler;
    }

    public function isShootNow(): bool {
        return $this->shoot_now;
    }

    public function getCoolDownTick(): int {
        return 0;
    }

    public function getRecoil(): float {
        return 0.05;
    }

    public function getDelayTick(): float {
        return 1;
    }

    public function getPeriodTick(): float {
        return 0.5;
    }

    public function getDamage(): int {
        return 3;
    }

    public function getKnockBack(): float {
        return 0.2;
    }

    public function getDistance(): int {
        return 25;
    }
}