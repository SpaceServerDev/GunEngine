<?php
declare(strict_types=1);

namespace space\yurisi\GunEngine\guns;

class RocketLauncher extends Gun {

    const MAX_AMMO = 1;

    public function getName(): string {
        return "RocketLauncher";
    }

    public function getReloadTick(): int {
        return 120;
    }

    public function getCoolDownTick(): int {
        return 0;
    }

    public function getRecoil(): float {
        return 0;
    }

    public function getDelayTick(): float {
        return 2;
    }

    public function getPeriodTick(): float {
        return 2;
    }

    public function getDamage(): int {
        return 0;
    }

    public function getKnockBack(): int {
        return 0;
    }

    public function getDistance(): int {
        return 40;
    }
}