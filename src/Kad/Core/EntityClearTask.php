<?php

declare(strict_types=1);

namespace Kad\Core;

use pocketmine\Server;
use pocketmine\entity\{
    Entity,
	Creature,
	Human,
	object\ExperienceOrb,
	object\ItemEntity
};
use pocketmine\scheduler\Task;

class EntityClearTask extends Task {

    private $plugin;

    public function __construct(Core $plugin){
        $this->plugin = $plugin;
    }
    public function onRun(int $tick) : void{
        foreach($this->plugin->getServer()->getLevels() as $level){
            foreach($level->getEntities() as $entity){
                if($entity instanceof ItemEntity){
                    $entity->flagForDespawn();
                }elseif($entity instanceof Creature && !$entity instanceof Human){
                    $entity->flagForDespawn();
                }elseif($entity instanceof ExperienceOrb){
                    $entity->flagForDespawn();
                }
            }
        }
    }
}