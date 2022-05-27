<?php
declare(strict_types=1);

namespace space\yurisi\GunEngine\guns;


class SniperRifle extends Gun {

    protected const MAX_AMMO = 5;

    public function getName(): string {
        return "SniperRifle";
    }

    public function getReloadTick(): int {
        return 100;
    }

    public function getCoolDownTick(): int {
        return 80;
    }

    public function getRecoil(): float {
        return 1;
    }

    public function getDelayTick(): float {
        return 2;
    }

    public function getPeriodTick(): float {
        return 2;
    }

    public function getDamage(): int {
        return 10;
    }

    public function getKnockBack(): float {
        return 0.5;
    }

    public function getDistance(): int {
        return 50;
    }
}