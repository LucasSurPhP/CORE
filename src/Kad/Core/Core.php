<?php
declare(strict_types=1);

namespace Kad\Core;

use pocketmine\{
	Player,
	Server
};
use pocketmine\block\Block;
use pocketmine\command\{
	Command,
	CommandSender
};
use pocketmine\entity\{
	Effect,
	EffectInstance,
	Entity
};
use pocketmine\event\{
	Listener,
	block\LeavesDecayEvent,
	entity\EntityLevelChangeEvent,
	player\PlayerJoinEvent,
	player\PlayerQuitEvent,
	player\PlayerDeathEvent
};
use pocketmine\item\{
	Item,
	WrittenBook
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
	
	public $fts = "§7[§4§lK§r§7]§r";

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
	 * @priority HIGH
	 */
	public function onJoin(PlayerJoinEvent $event){
		$player = $event->getPlayer();
		$name = $player->getName();
		$event->setJoinMessage("§7[§b§l+§r§7]§r§f " . "$name");
		$level = $this->getServer()->getLevelByName("hub");
		$x = -0.5;
		$y = 40;
		$z = -0.5;
		$pos = new Position($x, $y, $z, $level);
		$player->teleport($pos);
		$player->sendMessage($this->fts . TF::GOLD . " Welcome to MC Hangout Server [KYT]!");
		$player->setGamemode(2);
		$player->getLevel()->addSound(new GhastShootSound(new Vector3($player->getX(), $player->getY(), $player->getZ())));
	}
	/**
	 * @param PlayerQuitEvent $event
	 *
	 * @priority HIGH
	 */
	public function onQuit(PlayerQuitEvent $event){
		$player = $event->getPlayer();
		$name = $player->getName();
		$event->setQuitMessage("§7[§c§l-§r§7]§r§f " . "$name");
    }
    /**
     * @param PlayerDeathEvent $event
     * 
     * @priority HIGH
     */
    public function onDeath(PlayerDeathEvent $event) : bool{
		if(!$event->getPlayer()->hasPermission("core.lightning.use")){
			return false;
		}
        $this->Lightning($event->getPlayer());
		return true;
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
	/**
	 * @param LeavesDecayEvent $event
	 *
	 * @priority HIGHEST
	 */
	public function onDecay(LeavesDecayEvent $event){
		$event->setCancelled(true);
	}
	/**
	 * @param EntityLevelChangeEvent $event
	 *
	 * @priority HIGH
	 */
	// Ignore Minigame worlds such as Sumo/CTF as the Plugins for them handle necessary changes.
	public function onEntityLevelChange(EntityLevelChangeEvent $event){
		$entity = $event->getEntity();
		if($entity instanceof Player){
			$level = $event->getTarget()->getName();
			if($level === 'plots'){
				$entity->setGamemode(1);
				$x = 34.5;
				$y = 46;
				$z = 34.5;
				$level = $this->getServer()->getLevelByName("plots");
				$entity->setSpawn(new Position($x, $y, $z, $level));
			}elseif($level === 'hub'){
				$entity->setGamemode(2);
				$x = -0.5;
				$y = 40;
				$z = -0.5;
				$level = $this->getServer()->getLevelByName("hub");
				$entity->setSpawn(new Position($x, $y, $z, $level));
			}elseif($level === 'kitpvp'){
				$entity->setGamemode(0);
				$x = 283.5;
				$y = 47;
				$z = 202.5;
				$level = $this->getServer()->getLevelByName("kitpvp");
				$entity->setSpawn(new Position($x, $y, $z, $level));
			}elseif($level === 'city'){
				$entity->setGamemode(1);
				$x = 3;
				$y = 41;
				$z = 2;
				$level = $this->getServer()->getLevelByName("city");
				$entity->setSpawn(new Position($x, $y, $z, $level));
			}
		}else{
			$this->getServer()->getLogger()->info(TF::BLUE . "Yo what the fuck, a Non-Human entity just changed levels");
		}
	}

	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool{
		if($cmd->getName() == "gmc"){
			if($sender instanceof Player){
				if($sender->hasPermission("core.gmc.use")){
					$sender->setGamemode(1);
					$sender->getLevel()->addSound(new GhastShootSound(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
					$sender->sendMessage($this->fts . TF::GREEN . " Your gamemode has been set to creative!");
				}else{
					$sender->sendMessage($this->fts . TF::RED . " You do not have permission to use this command!");
				}
			}else{
				$sender->sendMessage("Please use this command in-game.");
			}
		}
		if($cmd->getName() == "gms"){
			if($sender instanceof Player){
				if($sender->hasPermission("core.gms.use")){
					$sender->setGamemode(0);
					$sender->getLevel()->addSound(new GhastShootSound(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
					$sender->sendMessage($this->fts . TF::GREEN . " Your gamemode has been set to Survival!");
				}else{
					$sender->sendMessage($this->fts . TF::RED . " You do not have permission to use this command!");
				}
			}else{
				$sender->sendMessage("Please use this command in-game.");
			}
		}
		if($cmd->getName() == "gma"){
			if($sender instanceof Player){
				if($sender->hasPermission("core.gma.use")){
					$sender->setGamemode(2);
					$sender->getLevel()->addSound(new GhastShootSound(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
					$sender->sendMessage($this->fts . TF::GREEN . " Your gamemode has been set to Adventure!");
				}else{
					$sender->sendMessage($this->fts . TF::RED . " You do not have permission to use this command!");
				}
			}else{
				$sender->sendMessage("Please use this command in-game.");
			}
		}
		if($cmd->getName() == "gmspc"){
			if($sender instanceof Player){
				if($sender->hasPermission("core.gmspc.use")){
					$sender->setGamemode(3);
					$sender->getLevel()->addSound(new GhastShootSound(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
					$sender->sendMessage($this->fts . TF::GREEN . " Your gamemode has been set to Spectator!");
				}else{
					$sender->sendMessage($this->fts . TF::RED . " You do not have permission to use this command!");
				}
			}else{
				$sender->sendMessage("Please use this command in-game.");
			}
		}
		if($cmd->getName() == "day"){
			if($sender instanceof Player){
				if($sender->hasPermission("core.day.use")){
					$sender->getLevel()->setTime(6000);
					$sender->getLevel()->addSound(new GhastShootSound(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
					$sender->sendMessage($this->fts . TF::GREEN . " Set the time to Day (6000) in your world!");
				}else{
					$sender->sendMessage($this->fts . TF::RED . " You do not have permission to use this command!");
				}
			}else{
				$sender->sendMessage("Please use this command in-game.");
			}
		}
		if($cmd->getName() == "night"){
			if($sender instanceof Player){
				if($sender->hasPermission("core.night.use")){
					$sender->getLevel()->setTime(16000);
					$sender->getLevel()->addSound(new GhastShootSound(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
					$sender->sendMessage($this->fts . TF::GREEN . " Set the time to Night (16000) in your world!");
				}else{
					$sender->sendMessage($this->fts . TF::RED . " You do not have permission to use this command!");
				}
			}else{
				$sender->sendMessage("Please use this command in-game.");
			}
		}
		if($cmd->getName() == "hub"){
			if($sender instanceof Player){
				$level = $this->getServer()->getLevelByName("hub");
				$x = 0;
				$y = 40;
				$z = 0;
				$pos = new Position($x, $y, $z, $level);
				$sender->teleport($pos);
				$sender->getLevel()->addSound(new EndermanTeleportSound(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
				$sender->sendMessage($this->fts . TF::GOLD . " Teleported to Hub");
				$sender->setGamemode(2);
			}else{
				$sender->sendMessage("You tried to use /hub via the Console? Man, you're a special kind of retard...");
			}
		}
		if($cmd->getName() == "clearinv"){
			if($sender instanceof Player){
				$sender->getInventory()->clearAll();
				$sender->getLevel()->addSound(new GhastShootSound(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
			}else{
				$sender->sendMessage("Please use this command in-game.");
			}
		}
		if($cmd->getName() == "lightning"){
			if($sender instanceof Player){
				if($sender->hasPermission("core.lightning.use")){
					$this->Lightning($sender);
				}else{
					$sender->sendMessage($this->fts . TF::RED . " You do not have permission to use this command!");
				}
			}else{
				$sender->sendMessage("Please run this command in-game.");
			}
		}
		if($cmd->getName() == "tpworld"){
			if($sender instanceof Player){
				if($sender->hasPermission("core.tpworld.use")){
					$world = strtolower($args[0]);
					$level = $this->getServer()->getLevelByName($world);
					$sender->teleport($level->getSafeSpawn());
					$sender->getLevel()->addSound(new GhastShootSound(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
					$sender->sendMessage($this->fts . TF::GREEN . " You have been teleported to " . TF::GOLD . $world);
				}else{
					$sender->sendMessage($this->fts . TF::RED . " You do not have permission to use this command!");
				}
			}else{
				$sender->sendMessage("Please run this command in-game.");
			}
		}
		if($cmd->getname() == "guide"){
			if($sender instanceof Player){
				/** @var WrittenBook $item */
				$item = Item::get(Item::WRITTEN_BOOK, 0, 1);
				$item->setTitle(TF::GREEN . "Guidebook");
				$item->setPageText(0, "§l§4   KYT Server Guide§r\n\n§3[KYT] MC hangout Server is a big place!\n§3There's lots of commands, builds, features and things to see and do.\n§3In the following pages you'll be introduced to them!");
				$item->setPageText(1, "§3KYT is made up of 2 key components: §6Plots §3& §2Minigames.\n\n§6Plots §3is the main world and is accessible by the following command:\n\n§5/hub\n\n§2Minigames §3are accessible via the §2Minigames Board §3at Hub. ");
				$item->setPageText(2, "§3To get started in Plots, do the following:\n\n§5/p auto\n§5/p claim\n\n§3Then, enjoy building!\n\n§3You can claim unlimited plots, and all plots are size 69x69 (nice!).");
				$item->setPageText(3, "§3Players have access to many commands, which are listed below:\n\n§5/day §0- §3Sets it to Day\n§5/night §0- §3Sets it to Night\n§5/lay §0- §3Lays you down\n§5/nick §0- §3Sets your Nickname\n§5/vehicles §0- §3Spawns a Car\n§5/weapon §0- §3Spawns a Gun");
				$item->setPageText(4, "§3VIPs have access to even more things!\n\n§5/lock §0- §3Locks a door or chest\n§5/ln §0- §3Summons lightning\n§5/give §0- §3Gives you Items\n\n§3VIP can be obtained through Giveaways and other Events on Discord.");
				$item->setPageText(5, "§3More Plot commands as well as §dFeatured Plots§3 can be found on the §6Plots Board §3at Hub!\n\n§2Minigames §3tend to swap in & out as Kaddicus tests them, so expect to see a variety!");
				$item->setPageText(6, "§3Below are some fixes for common isues you may have:\n\n§5Scoreboard is invasive/in the way/annoying. §0- §3Do /scorehud off\n§5Building is hard when it's dark! §0- §3Use §5/nv §3or §5/day §3as needed.");
				$item->setPageText(7, "§3We hope you enjoy your time here on KYT :D\n\n§9Kaddicus would like to thank BlueNinja123447 and LashedPopcorn24 for unknowingly motivating him to create this guidebook, and RexRed252807 for helping test and format it.");
				$item->setAuthor("Kaddicus");
				$sender->getInventory()->addItem($item);
			}else{
				$sender->sendMessage("Please use this command in-game.");
			}
		}
		if($cmd->getName() == "rules"){
			if($sender instanceof Player){
				$sender->sendMessage("§6§o§lServer Rules§r");
				$sender->sendMessage("§f- §eNo advertising in any way, shape or form. §c(§4Ban§c)");
				$sender->sendMessage("§f- §eNo Hacked/Modded Clients, or Texture Packs that allow cheating (X-Ray, ESP, etc). PvP Packs are allowed. §c(§4Kick, then Ban§c)");
				$sender->sendMessage("§f- §eNo NSFW/18+ Builds, Chat or Content. §c(§4Ban§c)");
				$sender->sendMessage("§f- §eNo asking for OP/Ranks/Perms. §c(§4Kick, then Ban§c)");
				$sender->sendMessage("§f- §eNo Drama. We've all had enough of it elsewhere, please do not bring it here. §c(§4Kick, then Ban§c)");
				$sender->sendMessage("§f- §eNo Lavacasts/Other excessive usages of Lava and Water. Generators are fine. §c(§4Ban§c)");
				$sender->sendMessage("§f- §eUse common sense. If it doesn't seem like a good idea, don't do it!§e");
				$sender->sendMessage("§f- §eThat's it, have fun §b:)§e");
			}else{
				$sender->sendMessage("Do you know why /rules is blocked for Console? Well, no, neither do I to be honest...");
			}
		}
		if($cmd->getName() == "nv"){
			if($sender instanceof Player){
				if($sender->getEffect(Effect::NIGHT_VISION)){
					$sender->sendMessage($this->fts . TF::DARK_RED . " Night Vision turned off!");
					$sender->removeEffect(Effect::NIGHT_VISION);
				}else{
					$sender->sendMessage($this->fts . TF::GREEN . " Night Vision turned on!");
					$sender->addEffect(new EffectInstance(Effect::getEffectByName("NIGHT_VISION"), INT32_MAX, 1, false));
				}
			}else{
				$sender->sendMessage($this->fts . TF::RED . " This command only works in game");
			}
		}
		return true;
	}
}