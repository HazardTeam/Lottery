<?php

/*
 * Copyright (c) 2024-2025 HazardTeam
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/HazardTeam/Lottery
 */

declare(strict_types=1);

namespace hazardteam\lottery\commands\subcommands;

use hazardteam\lottery\libs\_a851f5578cae0568\CortexPE\Commando\BaseSubCommand;
use hazardteam\lottery\Main;
use pocketmine\command\CommandSender;

class ReloadSubCommand extends BaseSubCommand {
	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
		$mainInstance = Main::getInstance();
		$mainInstance->reload();
		$sender->sendMessage($mainInstance->getMessage('reload-success'));
	}

	protected function prepare() : void {
		$this->setPermission('lottery.command.reload');
	}
}