<?php
declare(strict_types=1);

namespace space\yurisi\GunEngine\guns;

use pocketmine\scheduler\TaskHandler;

abstract class Gun {

    protected const MAX_AMMO = 30;

    protected bool $cool_down = false;

    protected bool $shoot_now = false;

    protected int $ammo;

    protected ?TaskHandler $handler;

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
        $this->cool_down = true;
    }

    public function endCoolDown() {
        $this->cool_down = false;
    }

    public function getCoolDown(): bool {
        return $this->cool_down;
    }

    public function isShootNow(): bool {
        return $this->shoot_now;
    }

    public function startShoot(TaskHandler $handler) {
        $this->shoot_now = true;
        $this->handler = $handler;
    }

    public function endShoot() {
        $this->shoot_now = false;
        $this->handler = null;
    }

    public function getTaskId(): ?TaskHandler {
        if (!$this->isShootNow()) {
            return null;
        }
        return $this->handler;
    }

    abstract public function getReloadTick(): int;

    abstract public function getDelayTick(): float;

    abstract public function getPeriodTick(): float;

    abstract public function getCoolDownTick(): int;

    abstract public function getRecoil(): float;

    abstract public function getDamage();

    abstract public function getKnockBack();

    abstract public function getDistance();


}