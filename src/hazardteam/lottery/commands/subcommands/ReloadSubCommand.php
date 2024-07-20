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

namespace hazardteam\lottery\commands\subcommands;

use hazardteam\lottery\libs\_6d19c0889371a630\CortexPE\Commando\BaseSubCommand;
use hazardteam\lottery\Main;
use pocketmine\command\CommandSender;

class ReloadSubCommand extends BaseSubCommand {
	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
		Main::getInstance()->reload();
	}

	protected function prepare() : void {
		$this->setPermission('lottery.reload');
	}
}