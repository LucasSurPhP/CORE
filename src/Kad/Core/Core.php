<?php
declare(strict_types = 1);

namespace Kad\Core;

use pocketmine\plugin\PluginBase;
use pocketmine\command\{
        Command,
        CommandSender
};
use pocketmine\utils\TextFormat as TF;
use pocketmine\level\Position;
use pocketmine\level\sound\{
        AnvilBreakSound,
        AnvilFallSound,     
        AnvilUseSound,      // This is the standard Ding/Chime
        BlazeShootSound,
        ClickSound,        // Standard Click like when opening Inventory
        DoorBumpSound,
        DoorCrashSound,    // ???
        DoorSound,
        EndermanTeleportSound,   // Similar to the Portal
        FizzSound,
        GenericSound,    // ???
        GhastShootSound,   // Think a rush of flame
        GhastSound,     // That Cat Shriek thingy
        LaunchSound,   // Arrows?
        PopSound,
        Sound
};
use pocketmine\event\{
        Listener,
        player\PlayerJoinEvent,
        player\PlayerQuitEvent,
        player\PlayerDeathEvent,
        player\PlayerRespawnEvent,
        player\PlayerInteractEvent,
        block\LeavesDecayEvent,
	entity\EntityLevelChangeEvent

};
use pocketmine\{Server, Player};
use pocketmine\entity\{Effect, EffectInstance};
use pocketmine\math\Vector3;
use pocketmine\tile\Sign;
use function array_diff;
use function scandir;

class Core extends PluginBase implements Listener{
    
    public $fts = "§7[§4§lK§r§7]§r";
    
    public function onEnable() {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
            foreach(array_diff(scandir($this->getServer()->getDataPath() . "worlds"), ["..", "."]) as $levelName){
                        if($this->getServer()->loadLevel($levelName)){
				$this->getLogger()->debug("Successfully loaded §6${levelName}");
                        }
            }
    }
    /**
     * @param PlayerJoinEvent $event
     * @priority HIGH
     */
    public function onJoin(PlayerJoinEvent $event) {
        $player = $event->getPlayer();
        $name = $player->getName();
        $event->setJoinMessage("§7[§b§l+§r§7]§r§f " . "$name");
        $player->setGamemode(1);
        $player->getLevel()->addSound(new GhastShootSound(new Vector3($player->getX(), $player->getY(), $player->getZ())));
    }
    /**
     * @param PlayerQuitEvent $event
     * @priority HIGH
     */   
    public function onQuit(PlayerQuitEvent $event) {
        $player = $event->getPlayer();
        $name = $player->getName();
        $event->setQuitMessage("§7[§c§l-§r§7]§r§f " . "$name");
    }
    /**
     * @param PlayerRespawnEvent $event
     * @priority LOWEST
     */
    public function onRespawn(PlayerRespawnEvent $event) {
        $player = $event->getPlayer();
        $world = $this->getServer()->getLevelByName("plots");
        $x = 34;
        $y = 46;
        $z = 34;
        $pos = new Position($x, $y, $z, $world);
        $event->setRespawnPosition($pos);
        $player->setGamemode(1);
    }
    /**
     * @param LeavesDecayEvent $event
     * @priority HIGHEST
     */
    public function onDecay(LeavesDecayEvent $event) {
        $event->setCancelled(true);
    }
    /**
     * @param EntityLevelChangeEvent $event
     * @priority HIGH
     */ 
    public function onEntityLevelChange(EntityLevelChangeEvent $event) {
	$entity = $event->getEntity();
        if($entity instanceof Player) {
		$level = $event->getTarget()->getName();
		if($level === 'plots') {
			$entity->setGamemode(1);
		}
	}
    }
    /**	
     * @param PlayerInteractEvent $event	
     * @priority LOWEST	
     */
    public function onInteract(PlayerInteractEvent $event){	
        if($event->getBlock()->getID() == 323 || $event->getBlock()->getID() == 63 || $event->getBlock()->getID() == 68){	
            $sign = $event->getPlayer()->getLevel()->getTile($event->getBlock());	
            $player = $event->getplayer();	
            if($player->hasPermission("core.worldsign.use")) {	
                if(!($sign instanceof Sign)){	
                    return;	
                }	
                $sign = $sign->getText();	
                if($sign[0]=='[WORLD]'){ 	
                    if(empty($sign[1]) !== true){	
                        $mapname = $sign[1];	
                        $event->getPlayer()->sendMessage($this->fts . " Preparing world '".$mapname."'");	
                        if(Server::getInstance()->loadLevel($mapname) != false){	
                            $event->getPlayer()->sendMessage($this->fts . " Teleporting...");	
                            $event->getPlayer()->teleport(Server::getInstance()->getLevelByName($mapname)->getSafeSpawn());	
                        }else{	
                            $event->getPlayer()->sendMessage($this->fts . " World '".$mapname."' not found.");	
                        }	
                    }	
                }	
            }	
        }	
    }
    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool
    {
        if($cmd->getName() == "gmc") {
            if($sender instanceof Player) {
                if($sender->hasPermission("core.gmc.use")) {
                    $sender->setGamemode(1);
                    $sender->getLevel()->addSound(new GhastShootSound(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
                    $sender->sendMessage($this->fts . TF::GREEN . " Your gamemode has been set to creative!");
                } else {
                    $sender->sendMessage($this->fts . TF::RED . " An error has occurred. Please notify a server administrator about this.");    
                }
            }
        }
        if($cmd->getName() == "gms") {
            if($sender instanceof Player) {
                if($sender->hasPermission("core.gms.use")) {
                    $sender->setGamemode(0);
                    $sender->getLevel()->addSound(new GhastShootSound(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
                    $sender->sendMessage($this->fts . TF::GREEN . " Your gamemode has been set to Survival!");
                } else {
                    $sender->sendMessage($this->fts . TF::RED . " An error has occurred. Please notify a server administrator about this.");
                }
            }
        }
        if($cmd->getName() == "gma") {
            if($sender instanceof Player) {
                if($sender->hasPermission("core.gma.use")) {
                    $sender->setGamemode(2);
                    $sender->getLevel()->addSound(new GhastShootSound(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
                    $sender->sendMessage($this->fts . TF::GREEN . " Your gamemode has been set to Adventure!");
                } else {
                    $sender->sendMessage($this->fts . TF::RED . " An error has occurred. Please notify a server administrator about this.");
                }
            }
        }
        if($cmd->getName() == "gmspc") {
            if($sender instanceof Player) {
                if($sender->hasPermission("core.gmspc.use")) {
                    $sender->setGamemode(3);
                    $sender->getLevel()->addSound(new GhastShootSound(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
                    $sender->sendMessage($this->fts . TF::GREEN . " Your gamemode has been set to Spectator!");
                } else {
                    $sender->sendMessage($this->fts . TF::RED . " An error has occurred. Please notify a server administrator about this.");
                }
            }
        }
        if($cmd->getName() == "day") {
            if($sender instanceof Player) {
                if($sender->hasPermission("core.day.use")) {
                    $sender->getLevel()->setTime(6000);
                    $sender->getLevel()->addSound(new GhastShootSound(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
                    $sender->sendMessage($this->fts . TF::GREEN . " Set the time to Day (6000) in your world!");
                } else {
                    $sender->sendMessage($this->fts . TF::RED . " An error has occurred. Please notify a server administrator about this.");
                }
            }
        }
        if($cmd->getName() == "night") {
            if($sender instanceof Player) {
                if($sender->hasPermission("core.night.use")) {
                    $sender->getLevel()->setTime(16000);
                    $sender->getLevel()->addSound(new GhastShootSound(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
                    $sender->sendMessage($this->fts . TF::GREEN . " Set the time to Night (16000) in your world!");
                } else {
                    $sender->sendMessage($this->fts . TF::RED . " An error has occurred. Please notify a server administrator about this.");
                }
            }
        }
        if($cmd->getName() == "hub") {
            if($sender instanceof Player) {
                $level = $this->getServer()->getLevelByName("plots");
                $x = 34;
                $y = 46;
                $z = 34;
                $pos = new Position($x, $y, $z, $level);
                $sender->teleport($pos);
                $sender->getLevel()->addSound(new EndermanTeleportSound(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
                $sender->sendMessage($this->fts . TF::GOLD . " Teleported to Hub");
                $sender->setGamemode(1);
            } else {
                $sender->sendMessage($this->fts . TF::RED . " An error has occurred. Please notify a server administrator about this.");
            }
        }
        if($cmd->getName() == "clearinv") {
            if($sender instanceof Player) {
                $sender->getInventory()->clearAll();
                $sender->getLevel()->addSound(new GhastShootSound(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
            } else {
                $sender->sendMessage($this->fts . TF::RED . " An error has occurred. Please notify a server administrator about this.");
            }
        }
        if($cmd->getName() == "rules") {
            if($sender instanceof Player) {
                $sender->sendMessage("§6§o§lServer Ruless§r");
                $sender->sendMessage("§f- §eNo advertising in any way, shape or form. §c(§4Ban§c)");
                $sender->sendMessage("§f- §eNo NSFW/18+ Builds, Chat or Content. §c(§4Ban§c)");
                $sender->sendMessage("§f- §eNo asking for OP/Ranks/Perms. §c(§4Kick, then Ban§c)");
                $sender->sendMessage("§f- §eNo Drama. We've all had enough of it elsewhere, please do not bring it here. §c(§4Kick, then Ban§c)");
                $sender->sendMessage("§f- §eNo Lavacasts/Other excessive usages of Lava and Water. Generators are fine. §c(§4Plot Reset for minor transgressions, otherwise Ban§c)");
                $sender->sendMessage("§f- §eUse common sense. If it doesn't seem like a good idea, don't do it!§e");
                $sender->sendMessage("§f- §eThat's it, have fun §b:)§e");
            }
        }
        if($cmd->getName() == "nv") {
            if($sender instanceof Player) {
                if($sender->getEffect(Effect::NIGHT_VISION)) {
                    $sender->sendMessage($this->fts . TF::DARK_RED . " Night Vision turned off!");
                    $sender->removeEffect(Effect::NIGHT_VISION);
            } else {
                $sender->sendMessage($this->fts . TF::GREEN . " Night Vision turned on!");
                $sender->addEffect(new EffectInstance(Effect::getEffectByName("NIGHT_VISION"), INT32_MAX, 1, false));
            }
        } else {
            $sender->sendMessage($this->fts . TF::RED . " This command only works in game");
            }  
        }     
    return true;
    }
}
