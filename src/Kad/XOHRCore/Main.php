<?php
namespace Kad\XOHRCore;
use pocketmine\plugin\PluginBase;
use pocketmine\command\{
        Command,
        CommandSender
};
use pocketmine\utils\TextFormat as TF;
use pocketmine\level\Position;
use pocketmine\event\{
        Listener,
        player\PlayerJoinEvent,
        player\PlayerQuitEvent,
        player\PlayerDeathEvent,
        player\PlayerRespawnEvent,
        block\BlockPlaceEvent,
        block\BlockBreakEvent,
        block\LeavesDecayEvent
};
use pocketmine\{Server, Player};
class Main extends PluginBase implements Listener{
    private $fts, $hubPosition, $hhubPosition;
    public function onEnable() {
        
        $this->saveResource('cfg.yml');
	      $this->cfg = new Config($this->getDataFolder() . "cfg.yml", CONFIG::YAML);
        
        $this->fts = $this->cfg->get("prefix");
        $h = $this->cfg->getNested("hub");
        $hh = $this->cfg->getNested("hybridhub");
            
        if(! Server::getInstance()->isLevelLoaded($h["world"]) ) Server::getInstance()->loadLevel($h["world"]);
        if(! Server::getInstance()->isLevelLoaded($hh["world"]) ) Server::getInstance()->loadLevel($hh["world"]);
            
        $this->hubPosition = new Position($h["x"], $h["y"], $h["z"], Server::getInstance()->getLevelByName($h["world"]));
        $this->hhubPosition = new Position($hh["x"], $hh["y"], $hh["z"], Server::getInstance()->getLevelByName($hh["world"]));
     
        Server::getInstance()->getPluginManager()->registerEvents($this, $this);
    }
    public function onJoin(PlayerJoinEvent $event) {
        $player = $event->getPlayer();
        $name = $player->getName();
        $event->setJoinMessage(str_replace("%gamertag", $name, $this->cfg->getNested("messages.join")));
        $player->teleport($this->hubPosition);
        $player->setGamemode(1);
    }   
    public function onQuit(PlayerQuitEvent $event) {
        $player = $event->getPlayer();
        $name = $player->getName();
        $event->setJoinMessage(str_replace("%gamertag", $name, $this->cfg->getNested("messages.quit")));
    }
    public function onDeath(PlayerDeathEvent $event) {
        $player = $event->getPlayer();
        $name = $player->getName();
        $event->setJoinMessage(str_replace("%gamertag", $name, $this->cfg->getNested("messages.death")));
    }
    public function onRespawn(PlayerRespawnEvent $event) {
        $event->setRespawnPosition($this->hubPosition);
    } 
    /**
     * @param BlockPlaceEvent $event
     * @priority HIGHEST
     */
    public function onPlace(BlockPlaceEvent $event){
        $player = $event->getPlayer();
        if(in_array($player->getLevel()->getFolderName(), $this->cfg->getNested("protected-worlds"))) {
            if(!$player->hasPermission("verified.user")){
                $event->getPlayer()->sendTip($this->cfg->getNested("messages.cant-place"));
                $event->setCancelled();
            }
        }
    }
    /**
     * @param BlockBreakEvent $event
     * @priority HIGHEST
     */
    public function onBreak(BlockBreakEvent $event) {
        $player = $event->getPlayer();
        if(in_array($player->getLevel()->getFolderName(), $this->cfg->getNested("protected-worlds"))) {
            if(!$player->hasPermission("verified.user")){
                $event->getPlayer()->sendTip($this->cfg->getNested("messages.cant-break"));
                $event->setCancelled();
            }
        }
    }
    /**
     * @param LeavesDecayEvent $event
     * @priority HIGHEST
     */
    public function onDecay(LeavesDecayEvent $event) {
        $event->setCancelled(true);
    }
    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool
    {
        if(!$sender instanceof Player) return true;
        switch($cmd->getName()) {
                case "gmc":
                if($sender->hasPermission("xohrcore.gmc.use")) {
                        $sender->setGamemode(1);
                        $sender->sendMessage($this->fts . TF::GREEN . "Your gamemode has been set to creative!");
                } else {
                        $sender->sendMessage(str_replace("%prefix", $this->fts, $this->cfg->getNested("messages.cmd-error")));
                }
                break;
                case "gms":
                if($sender->hasPermission("xohrcore.gms.use")) {
                         $sender->setGamemode(0);
                        $sender->sendMessage($this->fts . TF::GREEN . "Your gamemode has been set to creative!");
                } else {
                        $sender->sendMessage(str_replace("%prefix", $this->fts, $this->cfg->getNested("messages.cmd-error")));
                }
                break;
                case "gma":
                if($sender->hasPermission("xohrcore.gma.use")) {
                        $sender->setGamemode(2);
                        $sender->sendMessage($this->fts . TF::GREEN . "Your gamemode has been set to creative!");
                } else {
                        $sender->sendMessage(str_replace("%prefix", $this->fts, $this->cfg->getNested("messages.cmd-error")));
                }
                break;
                case "gmspc":
                if($sender->hasPermission("xohrcore.gmspc.use")) {
                        $sender->setGamemode(3);
                        $sender->sendMessage($this->fts . TF::GREEN . "Your gamemode has been set to creative!");
                } else {
                        $sender->sendMessage(str_replace("%prefix", $this->fts, $this->cfg->getNested("messages.cmd-error")));
                }
                break;
                case "day":
                if($sender->hasPermission("xohrcore.day.use")) {
                        $sender->getLevel()->setTime(6000);
                        $sender->sendMessage($this->fts . TF::GREEN . "Set the time to Day (6000) in your world!");
                } else {
                        $sender->sendMessage(str_replace("%prefix", $this->fts, $this->cfg->getNested("messages.cmd-error")));
                }
                break;
                case "night":
                if($sender->hasPermission("xohrcore.night.use")) {
                        $sender->getLevel()->setTime(16000);
                        $sender->sendMessage($this->fts . TF::GREEN . "Set the time to Night (16000) in your world!");
                } else {
                        $sender->sendMessage(str_replace("%prefix", $this->fts, $this->cfg->getNested("messages.cmd-error")));
                }
                break;
                case "hub":
                        $sender->teleport($this->hubPosition);
                        $sender->sendMessage($this->fts . TF::GOLD . "Teleported to Hub");
                break;
                case "hybridhub": case "hhub": case "h-h":
                        $sender->teleport($this->hhubPosition);
                        $sender->sendMessage($this->fts . TF::GOLD . "Teleported to Hybridian Prime");
                break;
                case "rules":
                        $sender->sendMessage("§6§o§lXOXO High RolePlay Rules§r");
                        $sender->sendMessage("§f- §eNo Advertising");
                        $sender->sendMessage("§f- §eNo NSFW");
                        $sender->sendMessage("§f- §eNo cursing. (Censoring words is allowed.)");
                        $sender->sendMessage("§f- §eNo asking for OP/Ranks/Perms");
                        $sender->sendMessage("§f- §eUse Common Sense. Failure to do so will not exempt you from punishment.");
                break;
                case "info":
                        $sender->sendMessage("§6§o§lXOXO High RP Rebooted Info§r");
                        $sender->sendMessage("§eXOXO High RolePlay is a server for the remnants");
                        $sender->sendMessage("§eof JM Pocket Creative, Nebula Games, XOXO High RolePlay, Neptune, Lapis Games, and Orion RolePlay PvP & Plots.");
                        $sender->sendMessage("§eThere are 5 Leaders, Kad, Cara, Becca, Skull, & Chocky.");
                        $sender->sendMessage("§eThe server is meant to bring together whoever is still there from the past, regardless of the various wars and drama that occured between them.");
                        $sender->sendMessage("§eDiscord Link: https://discord.gg/A64ZVAa");
                break;
                case "nv":
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
        return true;
    }
}
