<?php

namespace Kad\XOHRCore;

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
        block\LeavesDecayEvent,
        player\PlayerInteractEvent
};
use pocketmine\{Server, Player};
use pocketmine\entity\{Effect, EffectInstance};
use pocketmine\math\Vector3;
use pocketmine\tile\Sign;

class Core extends PluginBase implements Listener{
    
    public $fts = "§7[§dX§aO§dX§aO§7]§r";
    
    public function onEnable() {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }
    /**
     * @param PlayerJoinEvent $event
     * @priority HIGH
     */
    public function onJoin(PlayerJoinEvent $event) {
        $player = $event->getPlayer();
        $name = $player->getName();
        $event->setJoinMessage("§0• §7[§b+§7]§f" . "$name");
        $world = $this->getServer()->getLevelByName("world");
        $x = 210.5;
        $y = 68;
        $z = 90.5;
        $pos = new Position($x, $y, $z, $world);
        $player->teleport($pos);
        $player->setGamemode(1);
        $player->getLevel()->addSound(new FizzSound(new Vector3($player->getX(), $player->getY(), $player->getZ())));
    }
    /**
     * @param PlayerQuitEvent $event
     * @priority HIGH
     */   
    public function onQuit(PlayerQuitEvent $event) {
        $player = $event->getPlayer();
        $name = $player->getName();
        $event->setQuitMessage("§0• §7[§b-§7]§f" . "$name");
    }
    /**
     * @param PlayerDeathEvent $event
     * @priority LOWEST
     */
    public function onDeath(PlayerDeathEvent $event) {
        $player = $event->getPlayer();
        $name = $player->getName();
        $player->getLevel()->addSound(new GhastSound(new Vector3($player->getX(), $player->getY(), $player->getZ())));
        $event->setDeathMessage("§0• §7[§cX§7]§f" . "$name");
    }
    /**
     * @param PlayerRespawnEvent $event
     * @priority LOWEST
     */
    public function onRespawn(PlayerRespawnEvent $event) {
        $player = $event->getPlayer();
        $world = $this->getServer()->getLevelByName("world");
        $x = 210.5;
        $y = 68;
        $z = 90.5;
        $pos = new Position($x, $y, $z, $world);
        $player->teleport($pos);
        $player->setGamemode(1);
        $player->getLevel()->addSound(new FizzSound(new Vector3($player->getX(), $player->getY(), $player->getZ())));
    }
    /**
     * @param LeavesDecayEvent $event
     * @priority HIGHEST
     */
    public function onDecay(LeavesDecayEvent $event) {
        $event->setCancelled(true);
    }
    /**
     * @param PlayerInteractEvent $event
     * @priority LOWEST
     */
    public function onInteract(PlayerInteractEvent $event){
        if($event->getBlock()->getID() == 323 || $event->getBlock()->getID() == 63 || $event->getBlock()->getID() == 68){
            $sign = $event->getPlayer()->getLevel()->getTile($event->getBlock());
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
    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool
    {
        if($cmd->getName() == "gmc") {
            if($sender instanceof Player) {
                if($sender->hasPermission("xohrcore.gmc.use")) {
                    $sender->setGamemode(1);
                    $sender->getLevel()->addSound(new AnvilUseSound(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
                    $sender->sendMessage($this->fts . TF::GREEN . "Your gamemode has been set to creative!");
                } else {
                    $sender->sendMessage($this->fts . TF::RED . "An error has occurred. Please contact Jes'kad Ad'aryc#3845 on Discord to report this");    
                }
            }
        }
        if($cmd->getName() == "gms") {
            if($sender instanceof Player) {
                if($sender->hasPermission("xohrcore.gms.use")) {
                    $sender->setGamemode(0);
                    $sender->getLevel()->addSound(new AnvilUseSound(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
                    $sender->sendMessage($this->fts . TF::GREEN . "Your gamemode has been set to Survival!");
                } else {
                    $sender->sendMessage($this->fts . TF::RED . "An error has occurred. Please contact Jes'kad Ad'aryc#3845 on Discord to report this");
                }
            }
        }
        if($cmd->getName() == "gma") {
            if($sender instanceof Player) {
                if($sender->hasPermission("xohrcore.gma.use")) {
                    $sender->setGamemode(2);
                    $sender->getLevel()->addSound(new AnvilUseSound(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
                    $sender->sendMessage($this->fts . TF::GREEN . "Your gamemode has been set to Adventure!");
                } else {
                    $sender->sendMessage($this->fts . TF::RED . "An error has occurred. Please contact Jes'kad Ad'aryc#3845 on Discord to report this");
                }
            }
        }
        if($cmd->getName() == "gmspc") {
            if($sender instanceof Player) {
                if($sender->hasPermission("xohrcore.gmspc.use")) {
                    $sender->setGamemode(3);
                    $sender->getLevel()->addSound(new AnvilUseSound(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
                    $sender->sendMessage($this->fts . TF::GREEN . "Your gamemode has been set to Spectator!");
                } else {
                    $sender->sendMessage($this->fts . TF::RED . "An error has occurred. Please contact Jes'kad Ad'aryc#3845 on Discord to report this");
                }
            }
        }
        if($cmd->getName() == "day") {
            if($sender instanceof Player) {
                if($sender->hasPermission("xohrcore.day.use")) {
                    $sender->getLevel()->setTime(6000);
                    $sender->getLevel()->addSound(new AnvilUseSound(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
                    $sender->sendMessage($this->fts . TF::GREEN . "Set the time to Day (6000) in your world!");
                } else {
                    $sender->sendMessage($this->fts . TF::RED . "An error has occurred. Please contact Jes'kad Ad'aryc#3845 on Discord to report this");
                }
            }
        }
        if($cmd->getName() == "night") {
            if($sender instanceof Player) {
                if($sender->hasPermission("xohrcore.night.use")) {
                    $sender->getLevel()->setTime(16000);
                    $sender->getLevel()->addSound(new AnvilUseSound(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
                    $sender->sendMessage($this->fts . TF::GREEN . "Set the time to Night (16000) in your world!");
                } else {
                    $sender->sendMessage($this->fts . TF::RED . "An error has occurred. Please contact Jes'kad Ad'aryc#3845 on Discord to report this");
                }
            }
        }
        if($cmd->getName() == "hub") {
            if($sender instanceof Player) {
                $level = $this->getServer()->getLevelByName("world");
                $x = 210.5;
                $y = 68;
                $z = 90.5;
                $pos = new Position($x, $y, $z, $level);
                $sender->teleport($pos);
                $sender->getLevel()->addSound(new EndermanTeleportSound(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
                $sender->sendMessage($this->fts . TF::GOLD . "Teleported to Hub");
                $sender->setGamemode(1);
            } else {
                $sender->sendMessage($this->fts . TF::RED . "An error has occurred. Please contact Jes'kad Ad'aryc#3845 on Discord to report this");
            }
        }
        if($cmd->getName() == "clearinv") {
            if($sender instanceof Player) {
                $sender->getInventory()->clearAll();
                $sender->getLevel()->addSound(new GhastSound(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
            }
        }
        // Incoming area for Mega sized spam of Hogwarts Commands LOL
        if($cmd->getName() == "hogwarts") {
            if($sender instanceof Player) {
                $level = $this->getServer()->getLevelByName("Hogwarts");
                $x = -2035.5;
                $y = 121;
                $z = 421.5;
                $pos = new Position($x, $y, $z, $level);
                $sender->teleport($pos);
                $sender->getLevel()->addSound(new EndermanTeleportSound(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
                $sender->sendMessage($this->fts . TF::GOLD . "Teleported to Hogwarts");
            } else {
                $sender->sendMessage($this->fts . TF::RED . "An error has occurred. Please contact Jes'kad Ad'aryc#3845 on Discord to report this");
            }
        }
        if($cmd->getName() == "gryffindor") {
            if($sender instanceof Player) {
                $level = $this->getServer()->getLevelByName("Hogwarts");
                $x = -1953.5;
                $y = 132;
                $z = 481.5;
                $pos = new Position($x, $y, $z, $level);
                $sender->teleport($pos);
                $sender->getLevel()->addSound(new EndermanTeleportSound(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
                $sender->sendMessage($this->fts . TF::GOLD . "Apparated to Gryffindor Common Room");
            } else {
                $sender->sendMessage($this->fts . TF::RED . "An error has occurred. Please contact Jes'kad Ad'aryc#3845 on Discord to report this");
            }
        }
        if($cmd->getName() == "slytherin") {
            if($sender instanceof Player) {
                $level = $this->getServer()->getLevelByName("Hogwarts");
                $x = -2114.5;
                $y = 6;
                $z = 486.5;
                $pos = new Position($x, $y, $z, $level);
                $sender->teleport($pos);
                $sender->getLevel()->addSound(new EndermanTeleportSound(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
                $sender->sendMessage($this->fts . TF::GOLD . "Apparated to Slytherin Common Room");
            } else {
                $sender->sendMessage($this->fts . TF::RED . "An error has occurred. Please contact Jes'kad Ad'aryc#3845 on Discord to report this");
            }
        }
        if($cmd->getName() == "transfiguration") {
            if($sender instanceof Player) {
                $level = $this->getServer()->getLevelByName("Hogwarts");
                $x = -2031.5;
                $y = 132;
                $z = 549.5;
                $pos = new Position($x, $y, $z, $level);
                $sender->teleport($pos);
                $sender->getLevel()->addSound(new EndermanTeleportSound(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
                $sender->sendMessage($this->fts . TF::GOLD . "Apparated to Transfiguration Class");
            } else {
                $sender->sendMessage($this->fts . TF::RED . "An error has occurred. Please contact Jes'kad Ad'aryc#3845 on Discord to report this");
            }
        }
        if($cmd->getName() == "charms") {
            if($sender instanceof Player) {
                $level = $this->getServer()->getLevelByName("Hogwarts");
                $x = -2031.5;
                $y = 132;
                $z = 565.5;
                $pos = new Position($x, $y, $z, $level);
                $sender->teleport($pos);
                $sender->getLevel()->addSound(new EndermanTeleportSound(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
                $sender->sendMessage($this->fts . TF::GOLD . "Apparated to Charms Class");
            } else {
                $sender->sendMessage($this->fts . TF::RED . "An error has occurred. Please contact Jes'kad Ad'aryc#3845 on Discord to report this");
            }
        }
        if($cmd->getName() == "potions") {
            if($sender instanceof Player) {
                $level = $this->getServer()->getLevelByName("Hogwarts");
                $x = -2029.5;
                $y = 27;
                $z = 535.5;
                $pos = new Position($x, $y, $z, $level);
                $sender->teleport($pos);
                $sender->getLevel()->addSound(new EndermanTeleportSound(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
                $sender->sendMessage($this->fts . TF::GOLD . "Apparated to Potions Class");
            } else {
                $sender->sendMessage($this->fts . TF::RED . "An error has occurred. Please contact Jes'kad Ad'aryc#3845 on Discord to report this");
            }
        }
        if($cmd->getName() == "astronomy") {
            if($sender instanceof Player) {
                $level = $this->getServer()->getLevelByName("Hogwarts");
                $x = -2000.5;
                $y = 205;
                $z = 546.5;
                $pos = new Position($x, $y, $z, $level);
                $sender->teleport($pos);
                $sender->getLevel()->addSound(new EndermanTeleportSound(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
                $sender->sendMessage($this->fts . TF::GOLD . "Apparated to Astronomy Class");
            } else {
                $sender->sendMessage($this->fts . TF::RED . "An error has occurred. Please contact Jes'kad Ad'aryc#3845 on Discord to report this");
            }
        }
        if($cmd->getName() == "quidditch") {
            if($sender instanceof Player) {
                $level = $this->getServer()->getLevelByName("Hogwarts");
                $x = -1829.5;
                $y = 81;
                $z = 761.5;
                $pos = new Position($x, $y, $z, $level);
                $sender->teleport($pos);
                $sender->getLevel()->addSound(new EndermanTeleportSound(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
                $sender->sendMessage($this->fts . TF::GOLD . "Apparated to the Quidditch Pitch");
            } else {
                $sender->sendMessage($this->fts . TF::RED . "An error has occurred. Please contact Jes'kad Ad'aryc#3845 on Discord to report this");
            }
        }
        if($cmd->getName() == "hagrids") {
            if($sender instanceof Player) {
                $level = $this->getServer()->getLevelByName("Hogwarts");
                $x = -1700.5;
                $y = 71;
                $z = 373.5;
                $pos = new Position($x, $y, $z, $level);
                $sender->teleport($pos);
                $sender->getLevel()->addSound(new EndermanTeleportSound(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
                $sender->sendMessage($this->fts . TF::GOLD . "Apparated to Hagrids Hut");
            } else {
                $sender->sendMessage($this->fts . TF::RED . "An error has occurred. Please contact Jes'kad Ad'aryc#3845 on Discord to report this");
            }
        }
        // End of Hogwarts Commands :)
        if($cmd->getName() == "rules") {
            if($sender instanceof Player) {
                $sender->sendMessage("§6§o§lXOXO High RolePlay Server Rules§r");
                $sender->sendMessage("§f- §eNo Advertising");
                $sender->sendMessage("§f- §eNo NSFW");
                $sender->sendMessage("§f- §eNo cursing. (Censoring words is allowed.)");
                $sender->sendMessage("§f- §eNo asking for OP/Ranks/Perms");
                $sender->sendMessage("§f- §eUse Common Sense. Failure to do so will not exempt you from punishment.");
                $sender->getLevel()->addSound(new FizzSound(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
            }
        }
        if($cmd->getName() == "testcore") {
            if($sender instanceof Player) {
                $sender->sendMessage($this->fts . "• This is a test command that is used to test new shet. Ignore it please :)");
            }
        }
        if($cmd->getName() == "info") {
            if($sender instanceof Player) {
                $name = $sender->getName();
                $viewdist = $this->getServer()->getAllowedViewDistance();
                $defaultworld = $this->getServer()->getDefaultLevel();
                $apiversion = $this->getServer()->getApiVersion();
                $defaultgm = $this->getServer()->getDefaultGamemode();
                $pmversion = $this->getServer()->getPocketMineVersion();
                $tps = $this->getServer()->getTicksPerSecond();
                $sender->sendMessage("§d§lServer Status (Secondary Information)");
                $sender->sendMessage("§d§l§o• Requested by:§e " . $name . "§d§l§o•");
                $sender->sendMessage("§bView Distance: " . $viewdist);
                $sender->sendMessage("§bDefault World: " . $defaultworld);
                $sender->sendMessage("§bAPI Version: " . $apiversion);
                $sender->sendMessage("§bDefault Gamemode: " . $defaultgm);
                $sender->sendMessage("§bPocketMine-MP Version: " . $pmversion);
                $sender->sendMessage("§bTPS: " . $tps);
            }
        }
        if($cmd->getName() == "nv") {
            if($sender instanceof Player) {
                if($sender->getEffect(Effect::NIGHT_VISION)) {
                    $sender->sendMessage($this->fts . TF::DARK_RED . "NightVision turned off!");
                    $sender->removeEffect(Effect::NIGHT_VISION);
            } else {
                $sender->sendMessage($this->fts . TF::GREEN . "NightVision turned on!");
                $sender->addEffect(new EffectInstance(Effect::getEffectByName("NIGHT_VISION"), INT32_MAX, 1, false));
            }
        } else {
            $sender->sendMessage($this->fts . TF::RED . "This command only works in game");
            }  
        }     
    return true;
    }
}
