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

namespace hazardteam\lottery;

use hazardteam\lottery\libs\_231af81575281f7e\CortexPE\Commando\PacketHooker;
use hazardteam\lottery\libs\_231af81575281f7e\DaPigGuy\libPiggyEconomy\libPiggyEconomy;
use hazardteam\lottery\libs\_231af81575281f7e\DaPigGuy\libPiggyEconomy\providers\EconomyProvider;
use hazardteam\lottery\commands\LotteryCommand;
use InvalidArgumentException;
use hazardteam\lottery\libs\_231af81575281f7e\muqsit\invmenu\InvMenuHandler;
use pocketmine\plugin\DisablePluginException;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use Throwable;
use function count;
use function implode;
use function in_array;
use function is_array;
use function is_int;
use function is_numeric;
use function is_string;

class Main extends PluginBase {
	use SingletonTrait;

	/** @var array<string, mixed> */
	private array $economyConfig;
	private int $minBet;
	/** @var array<int, array{minRange: string, maxRange: string, chance: int}> */
	private array $range;
	/** @var array<string, string> */
	private array $messages;
	/** @var array<string, array<string, string>> */
	private array $forms;
	/**
	 * @var array<string, array{
	 * title: string,
	 * items: array<string, string>
	 * }>
	 */
	private array $gui;
	private string $lotteryCalculationMethod;

	private LotteryManager $lotteryManager;
	private EconomyProvider $economyProvider;

	public function onEnable() : void {
		self::setInstance($this);

		if (!InvMenuHandler::isRegistered()) {
			InvMenuHandler::register($this);
		}

		if (!PacketHooker::isRegistered()) {
			PacketHooker::register($this);
		}

		try {
			$this->loadConfig();
		} catch (Throwable $e) {
			$this->getLogger()->error('An error occurred while loading the configuration: ' . $e->getMessage());
			throw new DisablePluginException();
		}

		$this->lotteryManager = new LotteryManager($this->range);

		libPiggyEconomy::init();

		try {
			$this->economyProvider = libPiggyEconomy::getProvider($this->economyConfig);
		} catch (Throwable $e) {
			$this->getLogger()->critical('Failed to get economy provider: ' . $e->getMessage());
			throw new DisablePluginException();
		}

		$this->getServer()->getCommandMap()->register($this->getName(), new LotteryCommand($this, 'lottery', 'Try your hand at the Lottery and win big prizes!', ['ltry']));
	}

	/**
	 * Loads and validates the plugin configuration from the `config.yml` file.
	 * If the configuration is invalid, an exception will be thrown.
	 *
	 * @throws InvalidArgumentException when the configuration is invalid
	 */
	private function loadConfig() : void {
		$config = $this->getConfig();

		$economyConfig = $config->get('economy');
		if (!is_array($economyConfig) || !isset($economyConfig['provider']) || !is_string($economyConfig['provider'])) {
			throw new InvalidArgumentException('Invalid or missing "economy" configuration. Please provide an array with the key "provider" as a string.');
		}

		$this->economyConfig = $economyConfig;

		$minBet = $config->get('min-bet');
		if (!is_numeric($minBet) || (int) $minBet <= 0) {
			throw new InvalidArgumentException("Invalid minimum bet. 'min-bet' must be a positive number.");
		}

		$this->minBet = (int) $minBet;

		/** @var array<int, array{minRange: string, maxRange: string, chance: int}> $range */
		$range = $config->get('range', []);
		if (!is_array($range) || count($range) === 0) {
			throw new InvalidArgumentException("Invalid range settings. 'range' must be a non-empty array.");
		}

		foreach ($range as $index => $value) {
			if (!is_array($value)) {
				throw new InvalidArgumentException("Invalid range entry at index {$index}. Each entry must be an array.");
			}

			$minRange = $value['minRange'] ?? null;
			$maxRange = $value['maxRange'] ?? null;
			$chance = $value['chance'] ?? null;

			if (!is_string($minRange) || !is_numeric($minRange)
				|| !is_string($maxRange) || !is_numeric($maxRange)
				|| !is_int($chance) || $chance <= 0
			) {
				throw new InvalidArgumentException("Invalid range entry at index {$index}. 'minRange' and 'maxRange' must be numeric strings, and 'chance' must be a positive integer.");
			}
		}

		$this->range = $range;

		$lotteryCalculationMethod = $config->get('lottery-calculation-method', 'max');
		$validCalculationMethods = ['max', 'min', 'average', 'product'];
		if (!is_string($lotteryCalculationMethod) || !in_array($lotteryCalculationMethod, $validCalculationMethods, true)) {
			throw new InvalidArgumentException("Invalid 'lottery-calculation-method'. Must be one of: " . implode(', ', $validCalculationMethods) . '.');
		}

		$this->lotteryCalculationMethod = $lotteryCalculationMethod;

		$messages = $config->get('messages', []);
		if (!is_array($messages)) {
			throw new InvalidArgumentException("Invalid messages settings. 'messages' must be an array.");
		}

		$requiredMessages = [
			'transaction-failed', 'invalid-bet', 'receive-prize', 'receive-less-prize', 'loss-prize',
			'no-enough-money', 'less-than-min-bet', 'broadcast-message', 'break-even-prize', 'reload-success',
		];
		foreach ($requiredMessages as $messageKey) {
			if (!isset($messages[$messageKey]) || !is_string($messages[$messageKey])) {
				throw new InvalidArgumentException("Missing or invalid message for '{$messageKey}'.");
			}
		}

		$this->messages = $messages;

		$forms = $config->get('forms', []);
		if (!is_array($forms)) {
			throw new InvalidArgumentException("Invalid forms settings. 'forms' must be an array.");
		}

		$playForm = $forms['play'] ?? null;
		if (!is_array($playForm)) {
			throw new InvalidArgumentException("Invalid form 'play'. Must be an array.");
		}

		if (!isset($playForm['title']) || !is_string($playForm['title'])) {
			throw new InvalidArgumentException("Invalid form 'play'. 'title' must be provided and must be a string.");
		}

		if (!isset($playForm['content']) || !is_string($playForm['content'])) {
			throw new InvalidArgumentException("Invalid form 'play'. 'content' must be provided and must be a string.");
		}

		$this->forms = $forms;

		$gui = $config->get('gui', []);
		if (!is_array($gui)) {
			throw new InvalidArgumentException("Invalid GUI settings. 'gui' must be an array.");
		}

		$lotteryGui = $gui['lottery'] ?? null;
		if (!is_array($lotteryGui)) {
			throw new InvalidArgumentException("Invalid GUI 'lottery'. Must be an array.");
		}

		if (!isset($lotteryGui['title']) || !is_string($lotteryGui['title'])) {
			throw new InvalidArgumentException("Invalid GUI 'lottery'. 'title' must be provided and must be a string.");
		}

		$lotteryItems = $lotteryGui['items'] ?? null;
		if (!is_array($lotteryItems)) {
			throw new InvalidArgumentException("Invalid GUI 'lottery'. 'items' must be an array.");
		}

		if (!isset($lotteryItems['reveal']) || !is_string($lotteryItems['reveal'])) {
			throw new InvalidArgumentException("Invalid GUI 'lottery'. 'reveal' item must be provided and must be a string.");
		}

		if (!isset($lotteryItems['bet-info']) || !is_string($lotteryItems['bet-info'])) {
			throw new InvalidArgumentException("Invalid GUI 'lottery'. 'bet-info' item must be provided and must be a string.");
		}

		$revealGui = $gui['reveal'] ?? null;
		if (!is_array($revealGui)) {
			throw new InvalidArgumentException("Invalid GUI 'reveal'. Must be an array.");
		}

		if (!isset($revealGui['title']) || !is_string($revealGui['title'])) {
			throw new InvalidArgumentException("Invalid GUI 'reveal'. 'title' must be provided and must be a string.");
		}

		$revealItems = $revealGui['items'] ?? null;
		if (!is_array($revealItems)) {
			throw new InvalidArgumentException("Invalid GUI 'reveal'. 'items' must be an array.");
		}

		if (!isset($revealItems['reveal-result']) || !is_string($revealItems['reveal-result'])) {
			throw new InvalidArgumentException("Invalid GUI 'reveal'. 'reveal-result' item must be provided and must be a string.");
		}

		$this->gui = $gui;
	}

	public function reload() : void {
		$this->getConfig()->reload();
		try {
			$this->loadConfig();
		} catch (Throwable $e) {
			$this->getLogger()->error('An error occurred while reloading the configuration: ' . $e->getMessage());

			throw new DisablePluginException();
		}

		$this->lotteryManager = new LotteryManager($this->range);
	}

	public function getLotteryManager() : LotteryManager {
		return $this->lotteryManager;
	}

	public function getEconomyProvider() : EconomyProvider {
		return $this->economyProvider;
	}

	public function getMinBet() : int {
		return $this->minBet;
	}

	/**
	 * @return array<int, array{minRange: string, maxRange: string, chance: int}>
	 */
	public function getRange() : array {
		return $this->range;
	}

	public function getMessage(string $key) : string {
		return $this->messages[$key] ?? '';
	}

	public function getFormTitle(string $form) : string {
		return $this->forms[$form]['title'] ?? '';
	}

	public function getFormContent(string $form) : string {
		return $this->forms[$form]['content'] ?? '';
	}

	public function getGuiTitle(string $guiType) : string {
		return $this->gui[$guiType]['title'] ?? '';
	}

	public function getGuiItem(string $guiType, string $item) : string {
		return $this->gui[$guiType]['items'][$item] ?? '';
	}

	public function getLotteryCalculationMethod() : string {
		return $this->lotteryCalculationMethod;
	}
}