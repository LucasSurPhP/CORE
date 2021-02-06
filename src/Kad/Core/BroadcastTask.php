<?php

declare(strict_types=1);

namespace Kad\Core;

use pocketmine\scheduler\Task;

class BroadcastTask extends Task{

	public function onRun(int $tick) : void{
		$messages = Core::getInstance()->getConfig()->get("messages");
		$message = $messages[array_rand($messages)];
		$message = str_replace(array(
			"&",
			"{line}",
			"{max_players}",
			"{online_players}",
			"{tps}",
			"{motd}"
		), array(
			"§",
			"\n",
			Core::getInstance()->getServer()->getMaxPlayers(),
			count(Core::getInstance()->getServer()->getOnlinePlayers()),
			Core::getInstance()->getServer()->getTicksPerSecond(),
			Core::getInstance()->getServer()->getMotd()
		), $message);
		$prefix = str_replace("&", "§", Core::getInstance()->getConfig()->get("prefix"));
		Core::getInstance()->getServer()->broadcastMessage($prefix . $message);
	}
}