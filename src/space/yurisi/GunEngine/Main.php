<?php
declare(strict_types=1);

namespace space\yurisi\GunEngine;

use pocketmine\plugin\PluginBase;

class Main extends PluginBase {

    private GunManager $gunManager;

    protected function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->gunManager = new GunManager();
    }

    public function getGunManager():GunManager{
        return $this->gunManager;
    }
    protected function onDisable(): void {

    }
}