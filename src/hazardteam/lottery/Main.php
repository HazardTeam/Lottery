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

use CortexPE\Commando\PacketHooker;
use DaPigGuy\libPiggyEconomy\libPiggyEconomy;
use DaPigGuy\libPiggyEconomy\providers\EconomyProvider;
use hazardteam\lottery\commands\LotteryCommand;
use muqsit\invmenu\InvMenuHandler;
use pocketmine\plugin\DisablePluginException;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use function is_array;

class Main extends PluginBase {
	use SingletonTrait;

	private LotteryManager $lottmanager;
	private EconomyProvider $economyProvider;

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

		libPiggyEconomy::init();

		$economyConfig = $this->getConfig()->get('economy');
		if (!is_array($economyConfig) || !isset($economyConfig['provider'])) {
			$this->getLogger()->critical('Invalid or missing "economy" configuration. Please provide an array with the key "provider".');
			throw new DisablePluginException();
		}

		try {
			$this->economyProvider = libPiggyEconomy::getProvider($economyConfig);
		} catch (\Throwable $e) {
			$this->getLogger()->critical('Failed to get economy provider: ' . $e->getMessage());
			throw new DisablePluginException();
		}

		$this->getServer()->getCommandMap()->register('Lottery', new LotteryCommand($this, 'lottery', 'Try your hand at the Lottery and win big prizes!', ['ltry']));
	}

	public function reload() : void {
		$this->getConfig()->reload();
		$this->lottmanager = new LotteryManager();
	}

	public function getLotteryManager() : LotteryManager {
		return $this->lottmanager;
	}

	public function getEconomyProvider() : EconomyProvider {
		return $this->economyProvider;
	}
}
