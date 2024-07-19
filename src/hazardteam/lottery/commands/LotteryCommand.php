<?php

namespace hazardteam\lottery\commands;

use hazardteam\lottery\commands\subcommands\ReloadSubCommand;
use CortexPE\Commando\BaseCommand;
use hazardteam\lottery\commands\subcommands\PlaySubCommand;
use hazardteam\lottery\Main;

class LotteryCommand extends BaseCommand {
    
	/**
	 * @param \pocketmine\command\CommandSender $sender
	 * @param string $aliasUsed
	 * @param array $args
	 */
	public function onRun(\pocketmine\command\CommandSender $sender, string $aliasUsed, array $args): void {
        if(count($args) == 0) $sender->sendMessage("Usage: /lottery <play>");
	}
	
	/**
	 * This is where all the arguments, permissions, sub-commands, etc would be registered
	 */
	protected function prepare(): void {
        $this->setPermission("lottery.command");
		$plugin = $this->getOwningPlugin();

		\assert($plugin instanceof Main);

        $this->registerSubCommand(new PlaySubCommand($plugin, "play"));
		$this->registerSubCommand(new ReloadSubCommand($plugin, "reload"));
	}
}