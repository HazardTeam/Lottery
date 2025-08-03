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

use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\constraint\InGameRequiredConstraint;
use hazardteam\lottery\Main;
use InvalidArgumentException;
use jojoe77777\FormAPI\CustomForm;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\Wool;
use pocketmine\command\CommandSender;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskHandler;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\TextFormat;
use pocketmine\world\sound\AnvilUseSound;
use pocketmine\world\sound\ClickSound;
use pocketmine\world\sound\PopSound;
use pocketmine\world\sound\XpCollectSound;
use pocketmine\world\sound\XpLevelUpSound;
use function abs;
use function array_column;
use function array_rand;
use function array_reduce;
use function array_search;
use function array_sum;
use function array_values;
use function count;
use function implode;
use function in_array;
use function is_numeric;
use function max;
use function min;
use function range;
use function str_repeat;
use function str_replace;

class PlaySubCommand extends BaseSubCommand {
	/** @var array<int> */
	private array $innerSlot = [];

	/** @var array<string, array<array{color: DyeColor, multiplier: float|int}>> */
	private array $playerSelections = [];

	/** @var array<string, TaskHandler> */
	private array $activeTasks = [];

	/** @var array<DyeColor> */
	private array $lotteryColors;

	public function __construct(PluginBase $plugin, string $name, string $description = '', array $aliases = []) {
		parent::__construct($plugin, $name, $description, $aliases);
		$this->initializeSlots();
		$this->lotteryColors = [
			DyeColor::RED(), DyeColor::GREEN(), DyeColor::CYAN(),
			DyeColor::ORANGE(), DyeColor::LIGHT_BLUE(), DyeColor::LIME(),
			DyeColor::PURPLE(), DyeColor::MAGENTA(), DyeColor::YELLOW(),
		];
	}

	private function initializeSlots() : void {
		// Generate inner slots (excluding borders) for 6x9 inventory
		for ($row = 1; $row < 5; ++$row) {
			for ($col = 1; $col < 8; ++$col) {
				$this->innerSlot[] = $row * 9 + $col;
			}
		}
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
		if (!$sender instanceof Player) {
			throw new AssumptionFailedError(InGameRequiredConstraint::class . ' should have prevented this');
		}

		$this->showBetForm($sender);
	}

	private function showBetForm(Player $player) : void {
		$main = Main::getInstance();
		$economy = $main->getEconomyProvider();

		$economy->getMoney($player, function (float|int $balance) use ($main, $economy, $player) : void {
			$form = new CustomForm(function (Player $player, ?array $data) use ($main, $economy, $balance) : void {
				if ($data === null) {
					return;
				}

				$betInput = $data['bet'] ?? '';
				if (!is_numeric($betInput)) {
					$player->sendMessage($main->getMessage('invalid-bet'));
					return;
				}

				$bet = (int) $betInput;
				$minBet = $main->getMinBet();

				if ($bet < $minBet) {
					$player->sendMessage($main->getMessage('less-than-min-bet'));
					return;
				}

				if ($balance < $bet) {
					$player->sendMessage($main->getMessage('no-enough-money'));
					return;
				}

				$this->processBetAndStartGame($player, $bet, $economy);
			});

			$form->setTitle($main->getFormTitle('play'));
			$form->addLabel(str_replace('{money}', (string) $balance, $main->getFormContent('play')));
			$form->addInput('§6» §fPlace your bet:', (string) $main->getMinBet(), 'bet');
			$player->sendForm($form);
		});
	}

	private function processBetAndStartGame(Player $player, int $bet, $economy) : void {
		$economy->takeMoney($player, $bet, function (bool $success) use ($player, $bet) : void {
			if (!$success) {
				$player->sendMessage(Main::getInstance()->getMessage('transaction-failed'));
				return;
			}

			$this->startGameWithCountdown($player, $bet);
		});
	}

	private function startGameWithCountdown(Player $player, int $bet) : void {
		$playerName = $player->getName();
		$this->cleanupPlayerTasks($playerName);

		// Enhanced entrance effect
		$player->getWorld()->addSound($player->getPosition(), new XpLevelUpSound(10));
		$player->sendTitle('§6§l🎰 LOTTERY TIME! 🎰', '§eGet ready for some excitement!', 10, 40, 10);

		$countdown = 3;
		$this->activeTasks[$playerName] = Main::getInstance()->getScheduler()->scheduleRepeatingTask(
			new ClosureTask(function () use ($player, $bet, $playerName, &$countdown) : void {
				if (!$player->isOnline()) {
					$this->cleanupPlayerTasks($playerName);
					return;
				}

				if ($countdown > 0) {
					$messages = [
						3 => '§7🎲 Rolling the dice of fate...',
						2 => '§e⚡ Charging up the magic...',
						1 => '§a🍀 Lady Luck is watching!',
					];

					$dots = str_repeat('§6●', 4 - $countdown) . str_repeat('§8○', $countdown - 1);
					$player->sendTitle(
						'§6§l' . $countdown,
						$messages[$countdown] . "\n" . $dots,
						0,
						20,
						5
					);

					$player->getWorld()->addSound($player->getPosition(), new ClickSound());
					--$countdown;
				} else {
					$player->sendTitle('§a§l✨ LET\'S PLAY! ✨', '§fChoose your lucky blocks!', 0, 30, 10);
					$player->getWorld()->addSound($player->getPosition(), new AnvilUseSound());

					$this->cleanupPlayerTasks($playerName);
					Main::getInstance()->getScheduler()->scheduleDelayedTask(
						new ClosureTask(fn () => $this->showLotteryGUI($player, $bet)),
						30
					);
				}
			}),
			20
		);
	}

	private function showLotteryGUI(Player $player, int $bet) : void {
		$main = Main::getInstance();
		$table = $main->getLotteryManager()->getTables();
		$menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
		$menu->setName('§6§l🎰 ' . $main->getGuiTitle('lottery') . ' 🎰');

		$contents = $this->generateLotteryContents($bet, $table, $main);
		$menu->getInventory()->setContents($contents);

		$this->playerSelections[$player->getName()] = [];

		$menu->setListener(InvMenu::readonly(function (DeterministicInvMenuTransaction $tx) use ($bet, $table) : void {
			$this->handleLotteryClick($tx, $bet, $table);
		}));

		$menu->send($player);
		self::sendProgressMessage($player, 0);
	}

	private function generateLotteryContents(int $bet, array $table, Main $main) : array {
		$contents = [];

		// Fill borders with decorative blocks
		foreach (range(0, 53) as $slot) {
			if (in_array($slot, $this->innerSlot, true)) {
				// Create mystery wool blocks
				$color = $this->lotteryColors[array_rand($this->lotteryColors)];
				$wool = VanillaBlocks::WOOL()->setColor($color)->asItem();
				$wool->setLore([
					'§7§oClick to select this mystery block',
					'§6§l⚡ Hidden Multiplier Inside! ⚡',
					'§8' . str_repeat('▪', 20),
					'§d§oFortune favors the bold...',
				]);
				$contents[$slot] = $wool;
			} elseif ($slot === 48) {
				// Bet info
				$contents[$slot] = VanillaItems::GOLD_INGOT()
					->setCustomName('§6§lYour Bet: §e' . $bet . ' coins')
					->setLore(['§7Good luck, adventurer!', '§8May the odds be in your favor']);
			} elseif ($slot === 50) {
				// Reveal button (initially disabled)
				$contents[$slot] = VanillaBlocks::BARRIER()->asItem()
					->setCustomName('§c§lSelect 5 Blocks First!')
					->setLore(['§7Choose 5 mystery blocks', '§7before revealing your fate!']);
			} else {
				// Decorative border
				$contents[$slot] = VanillaBlocks::CRYING_OBSIDIAN()->asItem()
					->setCustomName('§8§l◆ §5Magic Barrier §8§l◆');
			}
		}

		return $contents;
	}

	private function handleLotteryClick(DeterministicInvMenuTransaction $tx, int $bet, array $table) : void {
		$player = $tx->getPlayer();
		$slot = $tx->getAction()->getSlot();
		$playerName = $player->getName();
		$menu = $tx->getAction()->getInventory();

		if (!isset($this->playerSelections[$playerName])) {
			$this->playerSelections[$playerName] = [];
		}

		$selectedCount = count($this->playerSelections[$playerName]);

		// Handle block selection
		if (in_array($slot, $this->innerSlot, true) && $selectedCount < 5) {
			$sourceItem = $tx->getAction()->getSourceItem();
			if (!$sourceItem->getBlock() instanceof Wool) {
				return;
			}

			$wool = $sourceItem->getBlock();
			$tableIndex = array_search($slot, $this->innerSlot, true);

			$this->playerSelections[$playerName][] = [
				'color' => $wool->getColor(),
				'multiplier' => $table[$tableIndex],
				'slot' => $slot,
			];

			// Transform selected block
			$selected = VanillaBlocks::GLAZED_TERRACOTTA()->setColor($wool->getColor())->asItem()
				->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 1))
				->setCustomName('§a§l✓ CHOSEN')
				->setLore(['§7This block is locked in!', '§6Awaiting revelation...']);

			$menu->setItem($slot, $selected);
			$player->getWorld()->addSound($player->getPosition(), new PopSound());

			$newCount = count($this->playerSelections[$playerName]);
			self::sendProgressMessage($player, $newCount);

			// Enable reveal button when 5 blocks selected
			if ($newCount === 5) {
				$revealBtn = VanillaItems::NETHER_STAR()
					->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 1))
					->setCustomName('§6§l🌟 REVEAL YOUR DESTINY! 🌟')
					->setLore([
						'§a§lAll 5 blocks selected!',
						'§7Click to discover your fortune!',
						'§6§oThe moment of truth awaits...',
					]);
				$menu->setItem(50, $revealBtn);

				$player->getWorld()->addSound($player->getPosition(), new XpLevelUpSound(15));
				$player->sendTitle('§a§l🎉 READY! 🎉', '§6Click the star to reveal!', 0, 40, 20);
			}
		}

		// Handle reveal button
		if ($slot === 50 && $selectedCount === 5) {
			$this->startRevealSequence($player, $bet);
			$player->removeCurrentWindow();
		}
	}

	private static function sendProgressMessage(Player $player, int $selected) : void {
		$bar = '§8[';
		for ($i = 0; $i < 5; ++$i) {
			$bar .= $i < $selected ? '§a■' : '§7□';
		}

		$bar .= "§8] §f{$selected}/5";

		$messages = [
			0 => '§7Choose your first lucky block!',
			1 => '§e4 more to go! Keep the magic flowing!',
			2 => '§e3 blocks left! You\'re doing great!',
			3 => '§a2 more! Almost there, champion!',
			4 => '§a§lOne final choice! Make it legendary!',
			5 => '§6§l✨ Perfect! Now reveal your destiny! ✨',
		];

		$player->sendActionBarMessage($bar . ' §8| ' . $messages[$selected]);
	}

	private function startRevealSequence(Player $player, int $bet) : void {
		$playerName = $player->getName();
		$this->cleanupPlayerTasks($playerName);

		$countdown = 4;
		$player->sendTitle('§e§l🔮 FORTUNE TELLING... 🔮', '§7The crystals are aligning...', 0, 30, 10);

		$this->activeTasks[$playerName] = Main::getInstance()->getScheduler()->scheduleRepeatingTask(
			new ClosureTask(function () use ($player, $bet, $playerName, &$countdown) : void {
				if (!$player->isOnline()) {
					$this->cleanupPlayerTasks($playerName);
					return;
				}

				if ($countdown > 0) {
					$suspense = [
						4 => '§7🌟 The stars are deciding...',
						3 => '§e⚡ Magic is building up...',
						2 => '§6🔥 The moment approaches...',
						1 => '§c§l💫 HERE WE GO!',
					];

					$intensity = 5 - $countdown;
					$sparkles = str_repeat('§6✦', $intensity) . str_repeat('§8○', $countdown);

					$player->sendTitle(
						'§6§l' . $countdown,
						$suspense[$countdown] . "\n" . $sparkles,
						0,
						20,
						5
					);

					$sound = $countdown <= 1 ? new XpLevelUpSound(10) : new ClickSound();
					$player->getWorld()->addSound($player->getPosition(), $sound);
					--$countdown;
				} else {
					$player->sendTitle('§a§l🎊 REVEALED! 🎊', '§fTime to see your fortune!', 0, 40, 20);
					$this->cleanupPlayerTasks($playerName);

					Main::getInstance()->getScheduler()->scheduleDelayedTask(
						new ClosureTask(fn () => $this->showRevealGUI($player, $bet)),
						40
					);
				}
			}),
			20
		);
	}

	private function showRevealGUI(Player $player, int $bet) : void {
		$main = Main::getInstance();
		$menu = InvMenu::create(InvMenu::TYPE_CHEST);
		$menu->setName('§6§l🎁 ' . $main->getGuiTitle('reveal') . ' 🎁');

		$selections = $this->playerSelections[$player->getName()] ?? [];
		$multipliers = [];
		$contents = [];

		// Fill with decorative items
		foreach (range(0, 26) as $i) {
			if ($i >= 10 && $i <= 14) {
				// Mystery boxes for each selection
				$key = $i - 10;
				if (isset($selections[$key])) {
					$mysteryBox = VanillaBlocks::ENDER_CHEST()->asItem()
						->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 1))
						->setCustomName('§5§l🎁 MYSTERY REWARD 🎁')
						->setLore([
							'§7Click to reveal your multiplier!',
							'§6§oWhat treasures lie within?',
							'§8' . str_repeat('✦', 20),
						]);
					$contents[$i] = $mysteryBox;
					$multipliers[$i] = $selections[$key]['multiplier'];
				}
			} else {
				$contents[$i] = VanillaBlocks::CRYING_OBSIDIAN()->asItem()
					->setCustomName('§8§l◆ Mystic Barrier ◆');
			}
		}

		$menu->getInventory()->setContents($contents);
		$this->processLotteryResults($player, $bet, $selections);

		$menu->setListener(InvMenu::readonly(function (DeterministicInvMenuTransaction $tx) use ($multipliers, $bet) : void {
			$this->handleRevealClick($tx, $multipliers, $bet);
		}));

		$menu->setInventoryCloseListener(function (Player $player) : void {
			$this->cleanup($player);
		});

		$menu->send($player);
	}

	private function handleRevealClick(DeterministicInvMenuTransaction $tx, array $multipliers, int $bet) : void {
		$player = $tx->getPlayer();
		$slot = $tx->getAction()->getSlot();
		$menu = $tx->getAction()->getInventory();

		if (!isset($multipliers[$slot])) {
			return;
		}

		$multiplier = $multipliers[$slot];
		$color = $multiplier >= 1 ? TextFormat::GREEN : ($multiplier > 0 ? TextFormat::GOLD : TextFormat::RED);

		$revealed = VanillaItems::PAPER()
			->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 1))
			->setCustomName($color . '§l' . $multiplier . 'x MULTIPLIER!')
			->setLore([
				'§7This mystery contained:',
				$color . '§l' . $multiplier . 'x reward',
				'§8' . str_repeat('▫', 15),
			]);

		$menu->setItem($slot, $revealed);

		$sound = $multiplier > 1 ? new XpLevelUpSound(20) : new XpCollectSound();
		$player->getWorld()->addSound($player->getPosition(), $sound);

		// Remove from player's selections
		if (isset($this->playerSelections[$player->getName()])) {
			$key = $slot - 10;
			unset($this->playerSelections[$player->getName()][$key]);
			$this->playerSelections[$player->getName()] = array_values($this->playerSelections[$player->getName()]);

			// Show final results when all revealed
			if (count($this->playerSelections[$player->getName()]) === 0) {
				$this->showFinalResults($menu, $player, $bet);
			}
		}
	}

	private function processLotteryResults(Player $player, int $bet, array $selections) : void {
		$main = Main::getInstance();
		$economy = $main->getEconomyProvider();

		$multipliers = array_column($selections, 'multiplier');
		$calculationMethod = $main->getLotteryCalculationMethod();
		[$totalMultiplier, $calculationMsg] = self::calculateMultiplier($multipliers, $calculationMethod);

		$prize = $bet * $totalMultiplier;
		$profit = $prize - $bet;

		if ($profit < 0) {
			$economy->getMoney($player, function (float|int $balance) use ($economy, $player, $profit) : void {
				$deduction = min(abs($profit), $balance);
				$economy->takeMoney($player, $deduction, function () : void {});
			});
		} else {
			$economy->giveMoney($player, $profit, function () : void {});
		}
	}

	private function showFinalResults(InvMenu $menu, Player $player, int $bet) : void {
		$selections = $this->playerSelections[$player->getName()] ?? [];
		$multipliers = array_column($selections, 'multiplier');

		$calculationMethod = Main::getInstance()->getLotteryCalculationMethod();
		[$totalMultiplier, $calculationMsg] = self::calculateMultiplier($multipliers, $calculationMethod);

		$prize = $bet * $totalMultiplier;
		$profit = $prize - $bet;
		$color = $profit >= 0 ? TextFormat::GREEN : TextFormat::RED;

		$finalItem = VanillaItems::NETHER_STAR()
			->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 1))
			->setCustomName($color . '§l' . ($profit >= 0 ? '+' : '') . $profit . ' COINS!')
			->setLore([
				'§7Final Result:',
				'§f' . $calculationMsg . ' = §6§l' . $totalMultiplier . 'x',
				'§7Bet: §e' . $bet . ' §7→ Prize: ' . $color . $prize,
				'§8' . str_repeat('═', 20),
				$profit > 0 ? '§a§l🎉 JACKPOT! 🎉' : ($profit === 0 ? '§e§l⚖ BREAK EVEN! ⚖' : '§c§l💔 NEXT TIME! 💔'),
			]);

		$menu->getInventory()->setItem(22, $finalItem);

		// Epic finale
		if ($profit > 0) {
			$player->getWorld()->addSound($player->getPosition(), new XpLevelUpSound(30));
			$player->sendTitle('§a§l🏆 WINNER! 🏆', '§6+' . $profit . ' coins earned!', 0, 60, 30);
		} else {
			$player->getWorld()->addSound($player->getPosition(), new AnvilUseSound());
			$player->sendTitle('§c§l🎯 TRY AGAIN! 🎯', '§7Better luck next time!', 0, 60, 30);
		}

		self::broadcastResult($player, $bet, $prize, $profit, $calculationMsg, $totalMultiplier);
	}

	private static function broadcastResult(Player $player, int $bet, float $prize, float $profit, string $calculation, float $multiplier) : void {
		$main = Main::getInstance();
		$status = $profit > 0 ? 'Win' : ($profit === 0 ? 'Break-even' : 'Loss');

		$message = str_replace(
			['{player}', '{prize}', '{earn}', '{bet}', '{calculation}', '{multiplier}', '{status}'],
			[$player->getName(), (string) $profit, (string) $prize, (string) $bet, $calculation, (string) $multiplier, $status],
			$main->getMessage('broadcast-message')
		);

		$player->getServer()->broadcastMessage($message);

		$resultKey = $profit > 0 ? 'receive-prize' : ($profit === 0 ? 'break-even-prize' : 'loss-prize');
		$player->sendMessage(str_replace('{prize}', (string) $profit, $main->getMessage($resultKey)));
	}

	private static function calculateMultiplier(array $multipliers, string $method) : array {
		if (count($multipliers) === 0) {
			return [0.0, 'No multipliers'];
		}

		return match ($method) {
			'max' => [(float) max($multipliers), 'Maximum(' . implode(', ', $multipliers) . ')'],
			'min' => [(float) min($multipliers), 'Minimum(' . implode(', ', $multipliers) . ')'],
			'average' => [array_sum($multipliers) / count($multipliers), 'Average(' . implode(', ', $multipliers) . ')'],
			'product' => [(float) array_reduce($multipliers, fn ($a, $b) => $a * $b, 1.0), 'Product(' . implode(', ', $multipliers) . ')'],
			default => throw new InvalidArgumentException('Invalid calculation method: ' . $method)
		};
	}

	private function cleanup(Player $player) : void {
		$playerName = $player->getName();
		unset($this->playerSelections[$playerName]);
		$this->cleanupPlayerTasks($playerName);
	}

	private function cleanupPlayerTasks(string $playerName) : void {
		if (isset($this->activeTasks[$playerName])) {
			$this->activeTasks[$playerName]->cancel();
			unset($this->activeTasks[$playerName]);
		}
	}

	protected function prepare() : void {
		$this->addConstraint(new InGameRequiredConstraint($this));
		$this->setPermission('lottery.command.play');
	}
}
