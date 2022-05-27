<?php
declare(strict_types=1);

namespace space\yurisi\GunEngine\task;

use Generator;
use pocketmine\scheduler\Task;

class EventGenerator extends Task {

    /**
     * @var Generator
     */
    private Generator $generator;

    /**
     * Actions to execute when run
     *
     * @param Generator $generator
     */
    public function __construct(Generator $generator) {
        $this->generator = $generator;
    }

    public function onRun():void {
        if ($this->generator->valid()) {
            $this->generator->next();
        } else {
            $this->getHandler()->cancel();
        }
    }
}