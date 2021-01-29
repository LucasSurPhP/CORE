<?php
declare(strict_types=1);

namespace Kad\Core;

use pocketmine\{
    Player,
    Server
};
use pocketmine\block\Block;
use pocketmine\command\{
    Commands,
    CommandSender
};
use pocketmine\entity\{
    Entity,
    Effect,
    EffectInstance
};
use pocketmine\event\{
    Listener,
    block\LeavesDecayEvent,
    entity\EntityLevelChangeEvent,
    player\PlayerJoinEvent,
    player\PlayerDeathEvent,
    player\PlayerQuitEvent
};
use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\level\Position;
use pocketmine\level\sound\{
	EndermanTeleportSound,
	GhastShootSound
};
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\{
	AddActorPacket,
	PlaySoundPacket
};
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat as TF;

use function array_diff;
use function scandir;

class Core extends PluginBase implements Listener{

    public $kyt = "§7[§4§lK§r§7]§r";

    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        foreach(array_diff(scandir($this->getServer()->getDataPath() . "worlds"), ["..", "."]) as $levelName){
            if($this->getServer()->loadLevel($levelName)){
                $this->getLogger()->debug("Successfully loaded §6${levelName}");
            }
        }
    }
    /**
     * @param PlayerJoinEvent $event
     * 
     * @priority LOW
     */
    public function Join(PlayerJoinEvent $event){
        $name = $event->getPlayer()->getName();
        $event->setJoinMessage("§7[§b§l+§r§7]§r§f " . "$name");
    }
    /**
     * @param PlayerQuitEvent $event
     * 
     * @priority LOW
     */
    public function Leave(PlayerQuitEvent $event){
        $name = $event->getPlayer()->getName();
        $event->setQuitMessage("§7[§c§l-§r§7]§r§f " . "$name");
    }
    /**
     * @param PlayerDeathEvent $event
     * 
     * @priority HIGH
     */
    public function Death(PlayerDeathEvent $event) : bool{
        if(!$event->getPlayer()->hasPermission("core.lightning.use")){
            return false;
        }
        $this->Lightning($event->getPlayer());
        return true;
    }
    # Removed until I decide how many worlds we'll have.
    /**
    * public function LevelChange(EntityLevelChangeEvent $event){
    *     $entity = $event->getEntity();
    *     if($entity instanceof Player){
    *       $level = $event->getTarget()->getName();
    *       if($level === 'city'){
    *           $entity->setGamemode(1);
    *       }
    *   }
    * }
    */
    public function Lightning(Player $player) : void{
        $light = new AddActorPacket();
		$light->type = "minecraft:lightning_bolt";
		$light->entityRuntimeId = Entity::$entityCount++;
		$light->metadata = [];
		$light->motion = null;
		$light->yaw = $player->getYaw();
		$light->pitch = $player->getPitch();
		$light->position = new Vector3($player->getX(), $player->getY(), $player->getZ());
		Server::getInstance()->broadcastPacket($player->getLevel()->getPlayers(), $light);
		$block = $player->getLevel()->getBlock($player->getPosition()->floor()->down());
		$particle = new DestroyBlockParticle(new Vector3($player->getX(), $player->getY(), $player->getZ()), $block);
		$player->getLevel()->addParticle($particle);
		$sound = new PlaySoundPacket();
		$sound->soundName = "ambient.weather.thunder";
		$sound->x = $player->getX();
		$sound->y = $player->getY();
		$sound->z = $player->getZ();
		$sound->volume = 1;
		$sound->pitch = 1;
		Server::getInstance()->broadcastPacket($player->getLevel()->getPlayers(), $sound);
    }
    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args ) :bool{
        switch(strtolower($cmd->getName())){
            case "gms":
                if($sender instanceof Player){
                    if($sender->hasPermission("core.gms.use")){
                        $sender->setGamemode(0);
                        $sender->getLevel()->addSound(new GhastShootSound(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
                        $sender->sendMessage($this->kyt . TF::GREEN . " Your gamemode has been set to Survival!");
                    }else{
                        $sender->sendMessage($this->kyt . TF::RED . " You do not have permission to use this command!");
                    }
                }else{
                    $sender->sendMessage("Please use this command in-game.");
            }
            return true;
            break;
            case "gmc":
                if($sender instanceof Player){
                    if($sender->hasPermission("core.gmc.use")){
                        $sender->setGamemode(1);
                        $sender->getLevel()->addSound(new GhastShootSound(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
                        $sender->sendMessage($this->kyt . TF::GREEN . " Your gamemode has been set to Creative!");
                    }else{
                        $sender->sendMessage($this->kyt . TF::RED . " You do not have permission to use this command!");
                    }
                }else{
                    $sender->sendMessage("Please use this command in-game.");
                }
            return true;
            break;
            case "gma":
                if($sender instanceof Player){
                    if($sender->hasPermission("core.gma.use")){
                        $sender->setGamemode(2);
                        $sender->getLevel()->addSound(new GhastShootSound(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
                        $sender->sendMessage($this->kyt . TF::GREEN . " Your gamemode has been set to Adventure!");
                    }else{
                        $sender->sendMessage($this->kyt . TF::RED . " You do not have permission to use this command!");
                    }
                }else{
                    $sender->sendMessage("Please use this command in-game.");
                }
            return true;
            break;
            case "gmspc":
                if($sender instanceof Player){
                    if($sender->hasPermission("core.gmspc.use")){
                        $sender->setGamemode(3);
                        $sender->getLevel()->addSound(new GhastShootSound(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
                        $sender->sendMessage($this->kyt . TF::GREEN . " Your gamemode has been set to Spectator!");
                    }else{
                        $sender->sendMessage($this->kyt . TF::RED . " You do not have permission to use this command!");
                    }
                }else{
                    $sender->sendMessage("Please use this command in-game.");
                }
            return true;
            break;
            case "day":
                if($sender instanceof Player){
                    if($sender->hasPermission("core.day.use")){
                        $sender->getLevel()->setTime(6000);
                        $sender->getLevel()->addSound(new GhastShootSound(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
                        $sender->sendMessage($this->kyt . TF::GREEN . " Time has been set to Day (6000) in your world.");
                    }else{
                        $sender->sendMessage($this->kyt . TF::RED . " You do not have permission to use this command!");
                    }
                }else{
                    $sender->sendMessage("Please use this command in-game.");
                }
            return true;
            break;
            case "night":
                if($sender instanceof Player){
                    if($sender->hasPermission("core.night.use")){
                        $sender->getLevel()->setTime(16000);
                        $sender->getLevel()->addSound(new GhastShootSound(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
                        $sender->sendMessage($this->kyt . TF::GREEN . " Time has been set to Night (16000) in your world.");
                    }else{
                        $sender->sendMessage($this->kyt . TF::RED . " You do not have permission to use this command!");
                    }
                }else{
                    $sender->sendMessage("Please use this command in-game.");
                }
            case "nv":
                if($sender instanceof Player){
                    if($sender->getEffect(EFFECT::NIGHT_VISION)){
                        $sender->sendMessage($this->kyt . TF::GREEN . " Night Vision turned off!");
					    $sender->removeEffect(Effect::NIGHT_VISION);
                    }else{
                        $sender->sendMessage($this->kyt . TF::GREEN . " Night Vision turned on!");
					    $sender->addEffect(new EffectInstance(Effect::getEffectByName("NIGHT_VISION"), INT32_MAX, 1, false));
                    }else{
                        $sender->sendMessage("This command only works in game");
                    }
                }
            return true;
            break;
            case "lightning":
                if($sender instanceof Player){
                    if($sender->hasPermission("core.lightning.use")){
                        $this->Lightning($sender);
                    }else{
                        $sender->sendMessage($this->kyt . TF::RED . " You do not have permission to use this command!");
                    }
                }else{
                    $sender->sendMessage("Please use this command in-game. We don't want the console fried by lightning. Idiot...");
                }
            return true;
            break;
            case "clearinv":
                if($sender instanceof Player){
                    $sender->getInventory()->clearAll();
                    $sender->getevel()->addSound(new GhastShootSound(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
                }else{
                    $sender->sendMessage("As messy as the servers inventory is, maybe lets not clear it, mk?");
                }
            return true;
            break;
            case "tpworld":
                if($sender instanceof Player){
                    if($sender->hasPermission("core.tpworld.use"){
                        $world = strtolower($args[0]);
                        $level = $this->getServer()->getLevelByName($world);
                        $sender->teleport($level->getSafeSpawn());
                        $sender->getLevel()->addSound(new EndermanTeleportSound(new Vector3($sender->getX(), $sendr->getY(), $sender->getZ())));
                        $sender->sendMessage($this->kyt . TF::GREEN . " You have been teleported to " . TF::GOLD . $world);
                    }else{
                        $sender->sendMessage($this->kyt . TF::RED . " You do not have permission to use this command!");
                    }
                }else{
                    $sender->sendMessage("Sir, you just tried to teleport a non-existent entity into a virtual game to teleport them to another world in said game. I recommend you go see a psychologist.");
                }
            return true;
            break;
            # All commands after this will likely need modifications more than once.
            case "hub":
                if($sender instanceof Player){
                    $sender->sendMessage($this->kyt . TF::GREEN . " This command is not ready yet :|");
                }else{
                    $sender->sendMessage("Sir, you just tried to teleport a non-existent entity into a virtual game to teleport them to another world in said game. I recommend you go see a psychologist.");
                }
            return true;
            break;
            case "rules":
                if($sender instanceof Player){
                    $sender->sendMessage("§6§o§lKYT Server Rules§r");
				    $sender->sendMessage("§f- §eNo advertising in any way, shape or form. §c(§4Ban§c)");
				    $sender->sendMessage("§f- §eNo NSFW/18+ Builds, Chat or Content. §c(§4Ban§c)");
				    $sender->sendMessage("§f- §eNo asking for OP/Ranks/Perms. §c(§4Kick, then Ban§c)");
				    $sender->sendMessage("§f- §eNo Drama. We've all had enough of it elsewhere, please do not bring it here. §c(§4Kick, then Ban§c)");
                    $sender->sendMessage("§f- §eNo Lavacasts/Other excessive usages of Lava and Water. §c(§4Ban§c)");
                    $sender->sendMessgae("§f- §eNo Dolphin Porn. §c(§4Ban§c)");
				    $sender->sendMessage("§f- §eThat's it, have fun §b:)§e");
                }else{
                    $sender->sendMessage("If you have console access you BETTER know the fucking rules...");
                }
            return true;
            break;
        }
    }
}