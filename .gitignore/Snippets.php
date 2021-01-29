<?php



        if($cmd->getname() == "guide"){
			if($sender instanceof Player){
				/** @var WrittenBook $item */
				$item = Item::get(Item::WRITTEN_BOOK, 0, 1);
				$item->setTitle(TF::GREEN . "Guidebook");
				$item->setPageText(0, "§l§4   KYT Server Guide§r\n\n§3[KYT] MC hangout Server is a big place!\n§3There's lots of commands, builds, features and things to see and do.\n§3In the following pages you'll be introduced to them!");
				$item->setPageText(1, "§3KYT is made up of 3 key components: §6Plots§3, §4Kit PvP §3& §2Minigames§3.\n\n§6Plots §3is accessible by the Plots portal.\n\n§4Kit PvP §3is accessible by the §4Kit PvP §3portal.\n\n§2Minigames §3are accessible via the §2Minigames §3portal. ");
				$item->setPageText(2, "§3To get started in §6Plots§3, do the following:\n\n§5/p auto\n§5/p claim\n\n§3Then, enjoy building!\n\n§3You can claim unlimited plots, and all plots are size 69x69 (nice!).");
				$item->setPageText(3, "§3To get started in §4Kit PvP§3, use §5/kit§3, select a Kit, then jump into the arena below. The following kits are available: \n\n§0Ninja\n§9Knight\n§6Warrior\n§aViking\n§cMad Scientist\n§dPlayer+§3/§eVIP");
				$item->setPageText(4, "§3To get started in §2Minigames§3, enter the Minigames Portal, and select a Game to join by tapping/clicking on a sign.");
				$item->setPageText(5, "§3Players have access to many commands, which are listed below:\n\n§5/day §0- §3Sets it to Day\n§5/night §0- §3Sets it to Night\n§5/lay §0- §3Lays you down\n§5/nick §0- §3Sets your Nickname\n§5/vehicles §0- §3Spawns a Car\n§5/weapon §0- §3Spawns a Gun");
				$item->setPageText(6, "§3To get §dPlayer+§3, vote for us! Upon voting, come in-game and do §5/vote§3. You will recieve §dPlayer+ §3for one day. Vote again to get it again!\n\n§eVIP §3can be obtained via Giveaways on Discord.");
				$item->setPageText(7, "§3Other Info:\n\nMore Plot commands as well as §dFeatured Plots§3 can be found on the §6Plots Board §3at §6Plots!!\n\n§2Minigames §3tend to swap in & out as Kaddicus tests them, so expect to see a variety!");
				$item->setPageText(8, "§3Below are some fixes for common isues you may have:\n\n§5Scoreboard is invasive/in the way/annoying.\n§0-\n§3Do §5/scorehud off\n\n§5Building is hard when it's dark!\n§0-\n§3Use §5/nv §3to get Night Vision.");
				$item->setPageText(9, "§3We hope you enjoy your time here on KYT :D\n\n§9Kaddicus would like to thank BlueNinja123447 and LashedPopcorn24 for motivating him to create this guidebook, and RexRed252807 for helping test and format it.");
				$item->setAuthor("Kaddicus");
				$sender->getInventory()->addItem($item);
			}else{
				$sender->sendMessage("Please use this command in-game.");
			}
		}