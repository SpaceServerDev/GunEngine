<?php
declare(strict_types=1);

namespace space\yurisi\GunEngine\guns;

use pocketmine\scheduler\TaskHandler;

class AssaultRifle extends Gun {

    protected const MAX_AMMO = 30;

    public function getName(): string {
        return "AssaultRifle";
    }

    public function getReloadTick(): int {
        return 50;
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