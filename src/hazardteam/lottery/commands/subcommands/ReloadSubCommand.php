<?php

namespace hazardteam\lottery\commands\subcommands;

use CortexPE\Commando\BaseSubCommand;
use hazardteam\lottery\Main;

class ReloadSubCommand extends BaseSubCommand {
    
	/**
	 * @param \pocketmine\command\CommandSender $sender
	 * @param string $aliasUsed
	 * @param array $args
	 */
	public function onRun(\pocketmine\command\CommandSender $sender, string $aliasUsed, array $args): void {
        Main::getInstance()->reload();
	}
	
	/**
	 * This is where all the arguments, permissions, sub-commands, etc would be registered
	 */
	protected function prepare(): void {
        $this->setPermission("lottery.reload");
	}
}