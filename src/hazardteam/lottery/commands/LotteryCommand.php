<?php

/*
 * Copyright (c) 2024 HazardTeam
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/HazardTeam/Lottery
 */

declare(strict_types=1);

namespace hazardteam\lottery\commands;

use hazardteam\lottery\libs\_8b1f1957a11fcbee\CortexPE\Commando\BaseCommand;
use hazardteam\lottery\commands\subcommands\PlaySubCommand;
use hazardteam\lottery\commands\subcommands\ReloadSubCommand;
use hazardteam\lottery\Main;
use function assert;
use function count;

class LotteryCommand extends BaseCommand {
	public function onRun(\pocketmine\command\CommandSender $sender, string $aliasUsed, array $args) : void {
		if (count($args) === 0) {
			$sender->sendMessage('Usage: /lottery <play>');
		}
	}

	/**
	 * This is where all the arguments, permissions, sub-commands, etc would be registered.
	 */
	protected function prepare() : void {
		$this->setPermission('lottery.command');
		$plugin = $this->getOwningPlugin();

		assert($plugin instanceof Main);

		$this->registerSubCommand(new PlaySubCommand($plugin, 'play'));
		$this->registerSubCommand(new ReloadSubCommand($plugin, 'reload'));
	}
}