<?php
declare(strict_types=1);

namespace Kad\Core;

use pocketmine\{
    Player,
    Server
};
use pocketmine\block\Block;
use pocketmine\tile\Sign;
use pocketmine\command\{
    Command,
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
	player\PlayerQuitEvent,
	player\PlayerInteractEvent
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
	
	/** @var array $signLines */
	protected $signLines = [];

	/** @var array $signText */
	protected $signText = [];

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
	public function Interact(PlayerInteractEvent $event) : void{
		$player = $event->getPlayer();
		$tile = $event->getPlayer()->getLevel()->getTile($event->getBlock());
		if($tile instanceof Sign){
			if(isset($this->signLines[$player->getName()]) && isset($this->signText[$player->getName()])){
				$tile->setLine($this->signLines[$player->getName()], $this->signText[$player->getName()]);
				$player->sendMessage($this->kyt . TF::GREEN . " You have successfully set line #" . strval($this->signLines[$player->getName()] + 1) . " of this sign");
				unset($this->signLines[$player->getName()]);
				unset($this->signText[$player->getName()]);
			}
		}
	}
	/**
	 * @param LeavesDecayEvent $event
	 * 
	 * @priority HIGHEST
	 */
	public function Decay(LeavesDecayEvent $event){
		$event->setCancelled(true);
	}
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
    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args ) :bool
    {
        if(strtolower($cmd->getName()) == "gms"){
			if($sender instanceof Player){
				if($sender->hasPermission("core.gms.use")){
					$sender->setGamemode(0);
					$sender->getLevel()->addSound(new GhastShootSound(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
					$sender->sendMessage($this->kyt . TF::GREEN . " Your gamemode has been set to Creative!");
				}else{
					$sender->sendMessage($this->kyt . TF::RED . " You do not have permission to use this command!");
				}
			}else{
				$sender->sendMessage("Please use this command in-game.");
			}
		}
		if(strtolower($cmd->getName()) == "gmc"){
			if($sender instanceof Player){
				if($sender->hasPermission("core.gmc.use")){
					$sender->setGamemode(1);
					$sender->getLevel()->addSound(new GhastShootSound(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
					$sender->sendMessage($this->kyt . TF::GREEN . " Your gamemode has been set to Survival!");
				}else{
					$sender->sendMessage($this->kyt . TF::RED . " You do not have permission to use this command!");
				}
			}else{
				$sender->sendMessage("Please use this command in-game.");
			}
		}
		if(strtolower($cmd->getName()) == "gma"){
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
		}
		if(strtolower($cmd->getName()) == "gmspc"){
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
		}
		if(strtolower($cmd->getName()) == "day"){
			if($sender instanceof Player){
				if($sender->hasPermission("core.day.use")){
					$sender->getLevel()->setTime(6000);
					$sender->getLevel()->addSound(new GhastShootSound(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
					$sender->sendMessage($this->kyt . TF::GREEN . " Set the time to Day (6000) in your world!");
				}else{
					$sender->sendMessage($this->kyt . TF::RED . " You do not have permission to use this command!");
				}
			}else{
				$sender->sendMessage("Please use this command in-game.");
			}
		}
		if(strtolower($cmd->getName()) == "night"){
			if($sender instanceof Player){
				if($sender->hasPermission("core.night.use")){
					$sender->getLevel()->setTime(16000);
					$sender->getLevel()->addSound(new GhastShootSound(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
					$sender->sendMessage($this->kyt . TF::GREEN . " Set the time to Night (16000) in your world!");
				}else{
					$sender->sendMessage($this->kyt . TF::RED . " You do not have permission to use this command!");
				}
			}else{
				$sender->sendMessage("Please use this command in-game.");
			}
		}
        if(strtolower($cmd->getName()) == "nv"){
			if($sender instanceof Player){
				if($sender->getEffect(Effect::NIGHT_VISION)){
					$sender->sendMessage($this->kyt . TF::GREEN . " Night Vision turned off!");
					$sender->removeEffect(Effect::NIGHT_VISION);
				}else{
					$sender->sendMessage($this->kyt . TF::GREEN . " Night Vision turned on!");
					$sender->addEffect(new EffectInstance(Effect::getEffectByName("NIGHT_VISION"), INT32_MAX, 1, false));
				}
			}else{
				$sender->sendMessage("This command only works in game");
			}
		}
        if(strtolower($cmd->getName()) == "clearinv"){
			if($sender instanceof Player){
				$sender->getInventory()->clearAll();
				$sender->getLevel()->addSound(new GhastShootSound(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
			}else{
				$sender->sendMessage("Please use this command in-game.");
			}
		}
        if(strtolower($cmd->getName()) == "tpworld"){
			if($sender instanceof Player){
				if($sender->hasPermission("core.tpworld.use")){
					$world = strtolower($args[0]);
					$level = $this->getServer()->getLevelByName($world);
					$sender->teleport($level->getSafeSpawn());
					$sender->getLevel()->addSound(new GhastShootSound(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
					$sender->sendMessage($this->kyt . TF::GREEN . " You have been teleported to " . TF::GOLD . $world);
				}else{
					$sender->sendMessage($this->kyt . TF::RED . " You do not have permission to use this command!");
				}
			}else{
				$sender->sendMessage("Sir, you just tried to teleport a non-existent entity into a virtual game to teleport them to another world in said game. I recommend you go see a psychologist.");
			}
		}
        if(strtolower($cmd->getName()) == "itemid"){
            if($sender instanceof Player){
                $item = $sender->getInventory()->getItemInHand()->getId();
                $damage = $sender->getInventory()->getItemInHand()->getDamage();
                $sender->sendMessage($this->kyt . TF::GREEN . " ID: " . $item . ":" . $damage);
            }else{
                $sender->sendMessage("Please use this command in-game.");
            }
        }
        if(strtolower($cmd->getName()) == "lightning"){
			if($sender instanceof Player){
				if($sender->hasPermission("core.lightning.use")){
					$this->Lightning($sender);
				}else{
					$sender->sendMessage($this->kyt . TF::RED . " You do not have permission to use this command!");
				}
			}else{
				$sender->sendMessage("Please run this command in-game.");
			}
		}
		if(strtolower($cmd->getName()) == "changesign"){
			if(!$sender instanceof Player){
				$sender->sendMessage("Please use this command in-game.");
				return false;
			}
			if(!$sender->hasPermission("core.changesign.use")){
				$sender->sendMessage($this->kyt . TF::RED . " You do not have permission to use this command!");
				return false;
			}
			if(empty($args[0])){
				$sender->sendMessage($this->kyt . TF::GREEN . " Usage: /cs <line #> <text>");
				return false;
			}
			switch($args[0]){
				case "1":
					$this->signLines[$sender->getName()] = 0;
					$this->signText[$sender->getName()] = implode(" ", array_slice($args, 1));
					$sender->sendMessage($this->kyt . TF::GREEN . " Tap a sign now to change the first line of text");
					break;
				case "2":
					$this->signLines[$sender->getName()] = 1;
					$this->signText[$sender->getName()] = implode(" ", array_slice($args, 1));
					$sender->sendMessage($this->kyt . TF::GREEN . " Tap a sign now to change the second line of text");
					break;
				case "3":
					$this->signLines[$sender->getName()] = 2;
					$this->signText[$sender->getName()] = implode(" ", array_slice($args, 1));
					$sender->sendMessage($this->kyt . TF::GREEN . " Tap a sign now to change the third line of text");
					break;
				case "4":
					$this->signLines[$sender->getName()] = 3;
					$this->signText[$sender->getName()] = implode(" ", array_slice($args, 1));
					$sender->sendMessage($this->kyt . TF::GREEN . " Tap a sign now to change the fourth line of text");
					break;
				default:
					$sender->sendMessage($this->kyt . TF::GRAY . " Usage: /cs <line #> <text>");
					break;
			}
		}
        # All commands after this will likely need modifications more than once.
		if(strtolower($cmd->getName()) == "hub"){
			if($sender instanceof Player){
				$sender->getLevel()->addSound(new EndermanTeleportSound(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
				$sender->sendMessage($this->kyt . TF::GOLD . " Teleported to Hub");
			}else{
				$sender->sendMessage("Sir, you just tried to teleport a non-existent entity into a virtual game to teleport them to another world in said game. I recommend you go see a psychologist.");
			}
		}
		if(strtolower($cmd->getName()) == "rules"){
			if($sender instanceof Player){
				$sender->sendMessage("§6§o§lKYT Server Rules§r");
				$sender->sendMessage("§f- §eNo advertising in any way, shape or form. §c(§4Ban§c)");
			    $sender->sendMessage("§f- §eNo NSFW/18+ Builds, Chat or Content. §c(§4Ban§c)");
			    $sender->sendMessage("§f- §eNo asking for OP/Ranks/Perms. §c(§4Kick, then Ban§c)");
			    $sender->sendMessage("§f- §eNo Drama. We've all had enough of it elsewhere, please do not bring it here. §c(§4Kick, then Ban§c)");
                $sender->sendMessage("§f- §eNo Lavacasts/Other excessive usages of Lava and Water. §c(§4Ban§c)");
                $sender->sendMessage("§f- §eNo Dolphin Porn. §c(§4Ban§c)");
			    $sender->sendMessage("§f- §eThat's it, have fun §b:)§e");
			}else{
				$sender->sendMessage("If you have console access you BETTER know the fucking rules...");
			}
		}
		return true;
	}
}