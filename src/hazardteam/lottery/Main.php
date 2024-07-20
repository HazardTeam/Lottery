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

namespace hazardteam\lottery;

use hazardteam\lottery\libs\_6d19c0889371a630\CortexPE\Commando\PacketHooker;
use hazardteam\lottery\commands\LotteryCommand;
use hazardteam\lottery\libs\_6d19c0889371a630\muqsit\invmenu\InvMenuHandler;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;

class Main extends PluginBase {
	use SingletonTrait;

	private LotteryManager $lottmanager;

	public function onEnable() : void {
		self::setInstance($this);

		$this->saveDefaultConfig();

		if (!InvMenuHandler::isRegistered()) {
			InvMenuHandler::register($this);
		}

		if (!PacketHooker::isRegistered()) {
			PacketHooker::register($this);
		}

		$this->lottmanager = new LotteryManager();

		$this->getServer()->getCommandMap()->register('Lottery', new LotteryCommand($this, 'lottery', 'Try your hand at the Lottery and win big prizes!', ['ltry']));
	}

	public function reload() : void {
		$this->getConfig()->reload();
		$this->lottmanager = new LotteryManager();
	}

	public function getLotteryManager() : LotteryManager {
		return $this->lottmanager;
	}
}