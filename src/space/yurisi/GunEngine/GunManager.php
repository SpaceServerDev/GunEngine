<?php
declare(strict_types=1);

namespace space\yurisi\GunEngine;

use pocketmine\Server;
use space\yurisi\GunEngine\guns\AssaultRifle;
use space\yurisi\GunEngine\guns\Gun;

class GunManager {

    private array $gun_data=[];

    private static GunManager $GunManager;

    public function __construct() {
        self::$GunManager=$this;
    }

    public function getInstance():GunManager{
        return self::$GunManager;
    }

    public function registerGun(string $gun,string $serial){
        switch ($gun){
            case "AssaultRifle":
                $this->gun_data[$gun][$serial]=new AssaultRifle();
                break;
        }
    }

    public function getGunData(string $gun,string $serial):Gun{
        if(!$this->isGunData($gun,$serial)){
            $this->registerGun($gun,$serial);
        }

        return $this->gun_data[$gun][$serial];
    }

    public function isGunData(string $gun,string $serial):bool {
        return !empty($this->gun_data[$gun][$serial]);
    }

    public static function getSerial(): string {
        return time().Server::getInstance()->getTick();
    }
}