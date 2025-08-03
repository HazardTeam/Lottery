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

use hazardteam\lottery\libs\_a155fcbf6eaf9375\CortexPE\Commando\BaseSubCommand;
use hazardteam\lottery\libs\_a155fcbf6eaf9375\CortexPE\Commando\constraint\InGameRequiredConstraint;
use hazardteam\lottery\Main;
use InvalidArgumentException;
use hazardteam\lottery\libs\_a155fcbf6eaf9375\jojoe77777\FormAPI\CustomForm;
use hazardteam\lottery\libs\_a155fcbf6eaf9375\muqsit\invmenu\InvMenu;
use hazardteam\lottery\libs\_a155fcbf6eaf9375\muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\Wool;
use pocketmine\command\CommandSender;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
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
	private array $chosen = [];

	/** @var array<string, int> */
	private array $playerCountdowns = [];

	/** @var array<string, int> */
	private array $selectionProgress = [];

	/** @var array<string, TaskHandler> */
	private array $playerCountdownTaskHandlers = [];

	public function __construct(PluginBase $plugin, string $name, string $description = '', array $aliases = []) {
		parent::__construct($plugin, $name, $description, $aliases);
		$this->initOffsets();
	}

	private function initOffsets() : void {
		$rows = 6;
		$cols = 9;

		for ($row = 1; $row < $rows - 1; ++$row) {
			for ($col = 1; $col < $cols - 1; ++$col) {
				$this->innerSlot[] = $row * $cols + $col;
			}
		}
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
		if (!$sender instanceof Player) {
			throw new AssumptionFailedError(InGameRequiredConstraint::class . ' should have prevented this');
		}

		$this->showPlayMenu($sender);
	}

	private function showPlayMenu(Player $player) : void {
		$mainInstance = Main::getInstance();
		$economyProvider = $mainInstance->getEconomyProvider();

		$economyProvider->getMoney($player, function (float|int $amount) use ($mainInstance, $economyProvider, $player) : void {
			$form = new CustomForm(function (Player $player, ?array $data) use ($mainInstance, $economyProvider, $amount) : void {
				if ($data === null) {
					return;
				}

				$betInput = $data['bet'] ?? null;
				if (!is_numeric($betInput)) {
					$player->sendMessage($mainInstance->getMessage('invalid-bet'));
					return;
				}

				$bet = (int) $betInput;
				$minBet = $mainInstance->getMinBet();

				if ($bet < $minBet) {
					$player->sendMessage($mainInstance->getMessage('less-than-min-bet'));
					return;
				}

				if ($amount < $bet) {
					$player->sendMessage($mainInstance->getMessage('no-enough-money'));
					return;
				}

				$economyProvider->takeMoney($player, $bet, function (bool $success) use ($player, $bet) : void {
					if (!$success) {
						$player->sendMessage(Main::getInstance()->getMessage('transaction-failed'));
						return;
					}

					// Enhanced loading sequence with sound and visual feedback
					$this->playLoadingSequence($player, $bet);
				});
			});

			$form->setTitle($mainInstance->getFormTitle('play'));
			$form->addLabel(str_replace('{money}', (string) $amount, $mainInstance->getFormContent('play')));
			$form->addInput('§6» §fPlace your bet:', default: (string) ($mainInstance->getMinBet()), label: 'bet');
			$player->sendForm($form);
		});
	}

	/**
	 * Enhanced loading sequence with visual and audio feedback.
	 */
	private function playLoadingSequence(Player $player, int $bet) : void {
		$playerName = $player->getName();

		if (isset($this->playerCountdownTaskHandlers[$playerName])) {
			$this->playerCountdownTaskHandlers[$playerName]->cancel();
			unset($this->playerCountdownTaskHandlers[$playerName]);
		}

		$this->playerCountdowns[$playerName] = 3;

		$player->getNetworkSession()->sendDataPacket(
			PlaySoundPacket::create('ambient.cave', $player->getPosition()->getX(), $player->getPosition()->getY(), $player->getPosition()->getZ(), 185.0, 1),
			true
		);

		$taskId = Main::getInstance()->getScheduler()->scheduleRepeatingTask(
			new ClosureTask(function () use ($player, $bet, $playerName) : void {
				if (!$player->isOnline()) {
					if (isset($this->playerCountdownTaskHandlers[$playerName])) {
						$this->playerCountdownTaskHandlers[$playerName]->cancel();
						unset($this->playerCountdownTaskHandlers[$playerName]);
					}

					unset($this->playerCountdowns[$playerName]);
					return;
				}

				if (!isset($this->playerCountdowns[$playerName])) {
					Main::getInstance()->getLogger()->error("Error: Player {$playerName} countdown data missing in task, cancelling task. Check for premature unsetting.");
					if (isset($this->playerCountdownTaskHandlers[$playerName])) {
						$this->playerCountdownTaskHandlers[$playerName]->cancel();
						unset($this->playerCountdownTaskHandlers[$playerName]);
					}

					return;
				}

				$countdown = $this->playerCountdowns[$playerName];

				if ($countdown > 0) {
					$dots = str_repeat('§6●', $countdown) . str_repeat('§8○', 3 - $countdown);
					$loadingBar = self::createLoadingBar(4 - $countdown, 3);

					$player->sendTitle(
						'§6§l' . $countdown,
						"§fPreparing lottery table...\n" . $loadingBar . "\n" . $dots,
						0,
						20,
						5
					);

					$player->getWorld()->addSound($player->getPosition(), new ClickSound());
					--$this->playerCountdowns[$playerName];
				} else {
					$player->sendTitle('§a§lREADY!', '§fSelect your lucky blocks!', 0, 30, 10);
					$player->getWorld()->addSound($player->getPosition(), new AnvilUseSound());

					if (isset($this->playerCountdownTaskHandlers[$playerName])) {
						$this->playerCountdownTaskHandlers[$playerName]->cancel();
						unset($this->playerCountdownTaskHandlers[$playerName]);
					}

					unset($this->playerCountdowns[$playerName]);

					Main::getInstance()->getScheduler()->scheduleDelayedTask(
						new ClosureTask(fn () => $this->showLotteryMenu($player, $bet)),
						30
					);
				}
			}),
			20
		);

		$this->playerCountdownTaskHandlers[$playerName] = $taskId;
	}

	/**
	 * Create a visual loading bar.
	 */
	private static function createLoadingBar(int $progress, int $total) : string {
		$filled = (int) (($progress / $total) * 20);
		$empty = 20 - $filled;

		return '§8[§a' . str_repeat('■', $filled) . '§7' . str_repeat('□', $empty) . '§8]';
	}

	/**
	 * Create a selection progress bar.
	 */
	private static function createSelectionProgress(int $selected, int $total = 5) : string {
		$progressBar = '§8[';
		for ($i = 0; $i < $total; ++$i) {
			if ($i < $selected) {
				$progressBar .= '§a■';
			} else {
				$progressBar .= '§7□';
			}
		}

		$progressBar .= "§8] §f{$selected}/{$total}";

		return $progressBar;
	}

	/**
	 * Enhanced wool block creation with tooltips.
	 */
	private static function createEnhancedWoolBlock(DyeColor $color, int $slot, array $table) : \pocketmine\item\Item {
		$wool = VanillaBlocks::WOOL()->setColor($color)->asItem();

		// Add mystical lore to create anticipation
		$lore = [
			'§7§oClick to select this block',
			'§8§o"Fortune favors the bold..."',
			'§6§l⚡ §r§6MYSTERY MULTIPLIER §6§l⚡',
			'§8' . str_repeat('▪', 25),
		];

		return $wool->setLore($lore);
	}

	private function showLotteryMenu(Player $player, int $bet) : void {
		$mainInstance = Main::getInstance();
		$table = $mainInstance->getLotteryManager()->getTables();
		$colors = [DyeColor::RED(), DyeColor::GREEN(), DyeColor::CYAN(), DyeColor::ORANGE(), DyeColor::LIGHT_BLUE(), DyeColor::LIME()];
		$contents = [];

		// Initialize selection progress
		$this->selectionProgress[$player->getName()] = 0;

		foreach (range(0, 53) as $i) {
			if (in_array($i, $this->innerSlot, true)) {
				$color = $colors[array_rand($colors)];
				$contents[$i] = self::createEnhancedWoolBlock($color, $i, $table);
			} elseif ($i === 48) {
				$contents[$i] = VanillaItems::BOOK()
					->setCustomName(str_replace('{bet}', (string) $bet, $mainInstance->getGuiItem('lottery', 'bet-info')))
					->setLore([
						'§7Your current wager',
						'§8Good luck, adventurer!',
					]);
			} elseif ($i === 50) {
				$contents[$i] = VanillaItems::GOLD_INGOT()
					->setCustomName($mainInstance->getGuiItem('lottery', 'reveal'))
					->setLore([
						"§7Click when you've selected",
						'§7all 5 blocks to reveal',
						'§7your fortune!',
						'§c§lSelect 5 blocks first!',
					]);
			} else {
				$contents[$i] = VanillaBlocks::VINES()->asItem();
			}
		}

		$menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
		$menu->setName($mainInstance->getGuiTitle('lottery'));
		$menu->getInventory()->setContents($contents);
		$menu->setListener(InvMenu::readonly(function (DeterministicInvMenuTransaction $transaction) use ($menu, $bet, $table) : void {
			$inventory = $menu->getInventory();
			$slot = $transaction->getAction()->getSlot();
			$player = $transaction->getPlayer();
			$playerName = $player->getName();
			$chosenWool = $transaction->getAction()->getSourceItem()->getBlock();

			if (!isset($this->chosen[$playerName])) {
				$this->chosen[$playerName] = [];
			}

			if (in_array($slot, $this->innerSlot, true) && count($this->chosen[$playerName]) < 5) {
				if (!$chosenWool instanceof Wool) {
					return;
				}

				$this->chosen[$playerName][] = ['color' => $chosenWool->getColor(), 'multiplier' => $table[array_search($slot, $this->innerSlot, true)]];

				// Enhanced selection visual with enchantment effect
				$selectedBlock = VanillaBlocks::GLAZED_TERRACOTTA()->setColor($chosenWool->getColor())->asItem()
					->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 1))
					->setCustomName('§a§l✓ SELECTED')
					->setLore([
						'§7This block has been chosen',
						'§6Awaiting revelation...',
						'§8' . str_repeat('★', 20),
					]);

				$inventory->setItem($slot, $selectedBlock);
				$player->getWorld()->addSound($player->getPosition(), new PopSound());

				// Update selection progress
				$this->selectionProgress[$playerName] = count($this->chosen[$playerName]);
				$this->updateSelectionProgress($player);

				// Special effects when 5 blocks are selected
				if (count($this->chosen[$playerName]) === 5) {
					self::onAllBlocksSelected($player, $menu, $inventory);
				}
			}

			if ($slot === 50) {
				if (count($this->chosen[$playerName]) === 5) {
					$this->startRevealSequence($player, $bet);
					$player->removeCurrentWindow();
				} else {
					// Play error sound and show message
					$player->getWorld()->addSound($player->getPosition(), new ClickSound());
					$remaining = 5 - count($this->chosen[$playerName]);
					$player->sendMessage("§c§l! §r§cYou need to select §e{$remaining} §cmore blocks before revealing!");
				}
			}
		}));

		$menu->send($player);

		// Send initial progress
		$this->updateSelectionProgress($player);
	}

	/**
	 * Update selection progress display.
	 */
	private function updateSelectionProgress(Player $player) : void {
		$selected = $this->selectionProgress[$player->getName()] ?? 0;
		$progressBar = self::createSelectionProgress($selected);

		$statusMessage = match ($selected) {
			0 => '§7Choose your first block!',
			1 => '§e4 more to go! Keep selecting!',
			2 => '§e3 blocks remaining!',
			3 => '§a2 blocks left! Almost there!',
			4 => '§a§lLast block! Make it count!',
			5 => '§6§lAll selected! Click the gold ingot!'
		};

		$player->sendActionBarMessage($progressBar . ' §8| §f' . $statusMessage);
	}

	/**
	 * Special effects when all blocks are selected.
	 */
	private static function onAllBlocksSelected(Player $player, InvMenu $menu, \pocketmine\inventory\Inventory $inventory) : void {
		// Transform vine blocks to indicate readiness
		foreach (range(0, 53) as $inventorySlot) {
			if ($inventory->getItem($inventorySlot)->equals(VanillaBlocks::VINES()->asItem())) {
				$readyBlock = VanillaBlocks::WEEPING_VINES()->asItem()
					->setCustomName('§a§lREADY TO REVEAL!')
					->setLore(['§7All blocks selected', '§6Fortune awaits...']);
				$inventory->setItem($inventorySlot, $readyBlock);
			}
		}

		// Update reveal button
		$revealButton = VanillaItems::GOLD_INGOT()
			->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 1))
			->setCustomName('§6§l⚡ REVEAL YOUR FORTUNE! ⚡')
			->setLore([
				'§a§lAll blocks selected!',
				'§7Click to discover your fate!',
				'§6May luck be with you...',
				'§8' . str_repeat('♦', 25),
			]);
		$inventory->setItem(50, $revealButton);

		// Play success sound and show celebration message
		$player->getWorld()->addSound($player->getPosition(), new XpLevelUpSound(10));
		$player->sendTitle('§a§lALL SELECTED!', '§6Click the gold ingot to reveal!', 0, 40, 20);
	}

	/**
	 * Start the reveal sequence with countdown and suspense.
	 */
	private function startRevealSequence(Player $player, int $bet) : void {
		$playerName = $player->getName();

		if (isset($this->playerCountdownTaskHandlers[$playerName])) {
			$this->playerCountdownTaskHandlers[$playerName]->cancel();
			unset($this->playerCountdownTaskHandlers[$playerName]);
		}

		$this->playerCountdowns[$playerName] = 5;

		$player->sendTitle('§e§lPREPARING REVEAL...', '§7The moment of truth approaches...', 0, 30, 10);

		$taskHandler = Main::getInstance()->getScheduler()->scheduleRepeatingTask(
			new ClosureTask(function () use ($player, $bet, $playerName) : void {
				if (!$player->isOnline()) {
					if (isset($this->playerCountdownTaskHandlers[$playerName])) {
						$this->playerCountdownTaskHandlers[$playerName]->cancel();
						unset($this->playerCountdownTaskHandlers[$playerName]);
					}

					unset($this->playerCountdowns[$playerName]); // Clean up state
					return;
				}

				if (!isset($this->playerCountdowns[$playerName])) {
					Main::getInstance()->getLogger()->error("Error: Player {$playerName} reveal countdown data missing in task, cancelling task. This shouldn't happen if player is online and previous unsets were managed correctly.");
					if (isset($this->playerCountdownTaskHandlers[$playerName])) {
						$this->playerCountdownTaskHandlers[$playerName]->cancel();
						unset($this->playerCountdownTaskHandlers[$playerName]);
					}

					return;
				}

				$countdown = $this->playerCountdowns[$playerName];

				if ($countdown > 0) {
					$suspenseMessage = match ($countdown) {
						5 => '§7Calculating your destiny...',
						4 => '§7The gods are deciding...',
						3 => '§e§lAlmost there...',
						2 => '§6§lLast chance to hope...',
						1 => '§c§lHERE WE GO!'
					};

					$intensity = 6 - $countdown;
					$dots = str_repeat('§6●', $intensity) . str_repeat('§8○', 5 - $intensity);

					$player->sendTitle(
						'§6§l' . $countdown,
						$suspenseMessage . "\n" . $dots,
						0,
						20,
						5
					);

					if ($countdown <= 2) {
						$player->getWorld()->addSound($player->getPosition(), new XpLevelUpSound(5));
					} else {
						$player->getWorld()->addSound($player->getPosition(), new ClickSound());
					}

					--$this->playerCountdowns[$playerName];
				} else {
					$player->sendTitle('§a§l✦ REVEALED! ✦', '§fTime to see your fortune!', 0, 40, 20);

					if (isset($this->playerCountdownTaskHandlers[$playerName])) {
						$this->playerCountdownTaskHandlers[$playerName]->cancel();
						unset($this->playerCountdownTaskHandlers[$playerName]);
					}

					unset($this->playerCountdowns[$playerName]);

					Main::getInstance()->getScheduler()->scheduleDelayedTask(
						new ClosureTask(fn () => $this->revealPrize($player, $bet)),
						40
					);
				}
			}),
			20
		);

		$this->playerCountdownTaskHandlers[$playerName] = $taskHandler;
	}

	private function revealPrize(Player $player, int $bet) : void {
		$mainInstance = Main::getInstance();
		$economyProvider = $mainInstance->getEconomyProvider();
		$menu = InvMenu::create(InvMenu::TYPE_CHEST);
		$menu->setName($mainInstance->getGuiTitle('reveal'));
		$contents = [];

		foreach (range(0, 26) as $i) {
			if ($i < 10 || $i > 16 || $i === 15) {
				$contents[$i] = VanillaBlocks::VINES()->asItem();
			}
		}

		/** @var array<float|int> $multipliers */
		$multipliers = [];
		$chosenPlayerItems = $this->chosen[$player->getName()] ?? [];

		foreach ($chosenPlayerItems as $key => $value) {
			$mysteryBlock = VanillaBlocks::GLAZED_TERRACOTTA()->setColor($value['color'])->asItem()
				->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 1))
				->setCustomName('§6§l✦ MYSTERY BLOCK ✦')
				->setLore([
					'§7Click to reveal the multiplier',
					'§8"What fortune lies within?"',
					'§6' . str_repeat('⟡', 15),
				]);
			$contents[$key + 10] = $mysteryBlock;
			$multipliers[$key + 10] = $value['multiplier'];
		}

		$calculationMethod = Main::getInstance()->getLotteryCalculationMethod();
		[$totalMultiplier, $calculationMessage] = self::calculateLotteryMultiplier($multipliers, $calculationMethod);
		$count = count($multipliers);

		$prize = $bet * $totalMultiplier;

		// Handle prize calculation
		if ($prize < 0) {
			$economyProvider->getMoney($player, function (float|int $amount) use ($economyProvider, $player, $prize, $mainInstance) : void {
				if ($amount < abs($prize)) {
					$economyProvider->setMoney($player, 0.0, function (bool $success) use ($player, $mainInstance) : void {
						if (!$success) {
							$player->sendMessage($mainInstance->getMessage('transaction-failed'));
						}
					});
				} else {
					$economyProvider->takeMoney($player, abs($prize), function (bool $success) use ($player, $mainInstance) : void {
						if (!$success) {
							$player->sendMessage($mainInstance->getMessage('transaction-failed'));
						}
					});
				}
			});
		} else {
			$economyProvider->giveMoney($player, $prize, function (bool $success) use ($player, $mainInstance) : void {
				if (!$success) {
					$player->sendMessage($mainInstance->getMessage('transaction-failed'));
				}
			});
		}

		$menu->getInventory()->setContents($contents);
		$menu->setListener(InvMenu::readonly(function (DeterministicInvMenuTransaction $transaction) use ($menu, $bet, $prize, $multipliers, $totalMultiplier, $count, $calculationMessage) : void {
			$player = $transaction->getPlayer();
			$playerName = $player->getName();
			$slot = $transaction->getAction()->getSlot();
			$sourceItem = $transaction->getAction()->getSourceItem();

			if (isset($multipliers[$slot]) && $slot >= 10 && $slot <= ($count + 9) && !$sourceItem->equals(VanillaItems::PAPER())) {
				$multiplier = $multipliers[$slot];

				if (isset($this->chosen[$playerName])) {
					foreach ($this->chosen[$playerName] as $key => $item) {
						if ($key + 10 === $slot) {
							unset($this->chosen[$playerName][$key]);
							$this->chosen[$playerName] = array_values($this->chosen[$playerName]);
							break;
						}
					}
				}

				// Enhanced reveal effect
				$revealedMultiplier = VanillaItems::PAPER()
					->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 1))
					->setCustomName(self::highlightTextColor($multiplier) . '§l' . (string) $multiplier . 'x MULTIPLIER')
					->setLore([
						'§7This block contained:',
						self::highlightTextColor($multiplier) . '§l' . (string) $multiplier . 'x',
						'§8' . str_repeat('▫', 20),
					]);

				$menu->getInventory()->setItem($slot, $revealedMultiplier);

				// Enhanced reveal sound
				if ($multiplier > 1) {
					$player->getWorld()->addSound($player->getPosition(), new XpLevelUpSound(15));
				} else {
					$player->getWorld()->addSound($player->getPosition(), new XpCollectSound());
				}

				// Show final results when all revealed
				if (count($this->chosen[$playerName] ?? []) === 0) {
					unset($this->chosen[$playerName]);
					self::showFinalResults($menu, $player, $prize, $bet, $totalMultiplier, $calculationMessage);
				}
			}
		}));

		$menu->setInventoryCloseListener(function (Player $player) use ($bet, $prize, $calculationMessage, $totalMultiplier, $mainInstance) : void {
			$this->handleGameCompletion($player, $bet, $prize, $calculationMessage, $totalMultiplier, $mainInstance);
		});

		$menu->send($player);
	}

	/**
	 * Show the final results with enhanced visual effects.
	 */
	private static function showFinalResults(InvMenu $menu, Player $player, float|int $prize, int $bet, float $totalMultiplier, string $calculationMessage) : void {
		$profit = $prize - $bet;
		$resultColor = self::highlightTextColor($prize, $bet);

		$finalResultItem = VanillaItems::PAPER()
			->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 1))
			->setCustomName($resultColor . '§l' . ($profit >= 0 ? '+' : '') . (string) $profit . ' COINS')
			->setLore([
				'§7Final Calculation:',
				'§f' . $calculationMessage . ' = ' . self::highlightTextColor($totalMultiplier) . '§l' . (string) $totalMultiplier . 'x',
				'§7Bet: §e' . $bet . ' §7→ Prize: ' . $resultColor . (string) $prize,
				'§8' . str_repeat('═', 25),
				$profit > 0 ? '§a§l🎉 CONGRATULATIONS! 🎉' : ($profit === 0 ? '§e§l⚖ BREAK EVEN ⚖' : '§c§l💔 BETTER LUCK NEXT TIME 💔'),
			]);

		$menu->getInventory()->setItem(16, $finalResultItem);

		// Dramatic final sound effect
		if ($profit > 0) {
			$player->getWorld()->addSound($player->getPosition(), new XpLevelUpSound(30));
			$player->sendTitle('§a§l🎊 WINNER! 🎊', '§6You won ' . abs($profit) . ' coins!', 0, 60, 30);
		} elseif ($profit === 0) {
			$player->getWorld()->addSound($player->getPosition(), new XpCollectSound());
			$player->sendTitle('§e§l⚖ BREAK EVEN ⚖', '§7No gain, no loss!', 0, 60, 30);
		} else {
			$player->getWorld()->addSound($player->getPosition(), new AnvilUseSound());
			$player->sendTitle('§c§l💔 LOSS 💔', '§cYou lost ' . abs($profit) . ' coins', 0, 60, 30);
		}
	}

	/**
	 * Handle game completion with cleanup.
	 */
	private function handleGameCompletion(Player $player, int $bet, float|int $prize, string $calculationMessage, float $totalMultiplier, Main $mainInstance) : void {
		$total = $prize - $bet;
		$status = $prize < $bet ? 'Loss' : 'Win';
		if ($prize === $bet) {
			$status = 'Break-even';
		}

		// Cleanup player data
		unset($this->chosen[$player->getName()], $this->selectionProgress[$player->getName()]);

		$broadcastMessage = str_replace(
			['{prize}', '{earn}', '{bet}', '{player}', '{calculation}', '{multiplier}', '{status}'],
			[(string) $total, (string) $prize, (string) $bet, $player->getName(), $calculationMessage, (string) $totalMultiplier, $status],
			$mainInstance->getMessage('broadcast-message')
		);
		$player->getServer()->broadcastMessage($broadcastMessage);

		if ($prize > $bet) {
			$player->sendMessage(str_replace('{prize}', (string) $total, $mainInstance->getMessage('receive-prize')));
		} elseif ($prize === $bet) {
			$player->sendMessage(str_replace('{prize}', (string) $total, $mainInstance->getMessage('break-even-prize')));
		} else {
			$player->sendMessage(str_replace('{prize}', (string) $total, $mainInstance->getMessage('loss-prize')));
		}
	}

	private static function highlightTextColor(float|int $value, int $goldCondition = 1) : string {
		return $value >= $goldCondition ? TextFormat::GREEN : ($value > 0 ? TextFormat::GOLD : TextFormat::RED);
	}

	/**
	 * Calculate the lottery multiplier based on the given option.
	 *
	 * @param array<float|int> $multipliers The array of multipliers
	 * @param string           $option      The calculation option ('max', 'min', 'average', 'product')
	 *
	 * @return array{0: float, 1: string}
	 *
	 * @throws InvalidArgumentException
	 */
	private static function calculateLotteryMultiplier(array $multipliers, string $option) : array {
		if (count($multipliers) === 0) {
			return [0.0, 'No multipliers selected'];
		}

		$totalMultiplier = 0.0;
		$calculationMessage = '';

		switch ($option) {
			case 'max':
				$totalMultiplier = (float) max($multipliers);
				$calculationMessage = 'Maximum';
				break;
			case 'min':
				$totalMultiplier = (float) min($multipliers);
				$calculationMessage = 'Minimum';
				break;
			case 'average':
				$totalMultiplier = array_sum($multipliers) / count($multipliers);
				$calculationMessage = 'Average';
				break;
			case 'product':
				$totalMultiplier = (float) array_reduce($multipliers, fn (float|int $last, float|int $current) : float|int => $last * $current, 1.0);
				$calculationMessage = 'Cumulative Multiplication';
				break;
			default:
				throw new InvalidArgumentException('Invalid option: ' . $option);
		}

		return [$totalMultiplier, $calculationMessage . '(' . implode(', ', $multipliers) . ')'];
	}

	protected function prepare() : void {
		$this->addConstraint(new InGameRequiredConstraint($this));
		$this->setPermission('lottery.command.play');
	}
}