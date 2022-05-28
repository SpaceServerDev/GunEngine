<?php
declare(strict_types=1);

namespace space\yurisi\GunEngine\guns;

class SubmachineGun extends Gun {

    const MAX_AMMO=40;

    public function getName(): string {
        return "SubmachineGun";
    }

    public function getReloadTick(): int {
        return 50;
    }

    public function getCoolDownTick(): int {
        return 0;
    }

    public function getRecoil():float {
        return 0.08;
    }

    public function getDelayTick(): float {
        return 0.2;
    }

    public function getPeriodTick(): float {
        return 0.4;
    }

    public function getDamage():int {
        return 4;
    }

    public function getKnockBack():float {
        return 0.2;
    }

    public function getDistance():int {
        return 20;
    }
}