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

use hazardteam\lottery\libs\_7d799c7bec630fa1\CortexPE\Commando\BaseSubCommand;
use hazardteam\lottery\libs\_7d799c7bec630fa1\CortexPE\Commando\constraint\InGameRequiredConstraint;
use hazardteam\lottery\Main;
use InvalidArgumentException;
use hazardteam\lottery\libs\_7d799c7bec630fa1\jojoe77777\FormAPI\CustomForm;
use hazardteam\lottery\libs\_7d799c7bec630fa1\muqsit\invmenu\InvMenu;
use hazardteam\lottery\libs\_7d799c7bec630fa1\muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
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
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\TextFormat;
use pocketmine\world\sound\PopSound;
use pocketmine\world\sound\XpCollectSound;
use pocketmine\world\sound\XpLevelUpSound;
use function abs;
use function array_rand;
use function array_reduce;
use function array_search;
use function array_sum;
use function count;
use function implode;
use function in_array;
use function is_numeric;
use function max;
use function min;
use function str_replace;

class PlaySubCommand extends BaseSubCommand {
	/** @var array<int> */
	private array $innerSlot = [];

	/** @var array<string, array<array{color: DyeColor, multiplier: float|int}>> */
	private array $chosen = [];

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
		$economyProvider = Main::getInstance()->getEconomyProvider();
		$economyProvider->getMoney($player, function (float|int $amount) use ($economyProvider, $player) : void {
			$form = new CustomForm(function (Player $player, $data = null) use ($economyProvider, $amount) : void {
				if ($data === null) {
					return;
				}

				if (!is_numeric($data['bet'])) {
					$player->sendMessage(Main::getInstance()->getMessage('invalid-bet'));
					return;
				}

				$bet = (int) $data['bet'];
				$minBet = Main::getInstance()->getMinBet();

				if ($bet < $minBet) {
					$player->sendMessage(Main::getInstance()->getMessage('less-than-min-bet'));
					return;
				}

				if ($amount < $bet) {
					$player->sendMessage(Main::getInstance()->getMessage('not-enough-money'));
					return;
				}

				$economyProvider->takeMoney($player, $bet, function (bool $success) use ($player, $bet) : void {
					if (!$success) {
						$player->sendMessage(Main::getInstance()->getMessage('transaction-failed'));
						return;
					}

					$pk = PlaySoundPacket::create('ambient.cave', $player->getPosition()->getX(), $player->getPosition()->getY(), $player->getPosition()->getZ(), 185.0, 1);
					$player->getNetworkSession()->sendDataPacket($pk, true);
					$this->showLotteryMenu($player, $bet);
				});
			});

			$form->setTitle(Main::getInstance()->getFormTitle('play'));
			$form->addLabel(str_replace('{money}', (string) $amount, Main::getInstance()->getFormContent('play')));
			$form->addInput('§6» §fPlace your bet:', default: (string) (Main::getInstance()->getMinBet()), label: 'bet');
			$player->sendForm($form);
		});
	}

	private function showLotteryMenu(Player $player, int $bet) : void {
		$table = Main::getInstance()->getLotteryManager()->getTables();
		$colors = [DyeColor::RED(), DyeColor::GREEN(), DyeColor::CYAN(), DyeColor::ORANGE(), DyeColor::LIGHT_BLUE(), DyeColor::LIME()];
		$contents = [];

		for ($i = 0; $i <= 53; ++$i) {
			if (in_array($i, $this->innerSlot, true)) {
				$color = $colors[array_rand($colors)];
				$contents[$i] = VanillaBlocks::WOOL()->setColor($color)->asItem();
			} elseif ($i === 48) {
				$contents[$i] = VanillaItems::BOOK()->setCustomName(str_replace('{bet}', (string) $bet, Main::getInstance()->getGuiItem('lottery', 'bet-info')));
			} elseif ($i === 50) {
				$contents[$i] = VanillaItems::GOLD_INGOT()->setCustomName(Main::getInstance()->getGuiItem('lottery', 'reveal'));
			} else {
				$contents[$i] = VanillaBlocks::VINES()->asItem();
			}
		}

		$menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
		$menu->setName(Main::getInstance()->getGuiTitle('lottery'));
		$menu->getInventory()->setContents($contents);
		$menu->setListener(InvMenu::readonly(function (DeterministicInvMenuTransaction $transaction) use ($menu, $bet, $table) : void {
			$inventory = $menu->getInventory();
			$slot = $transaction->getAction()->getSlot();
			$playerName = $transaction->getPlayer()->getName();
			$chosenWool = $transaction->getAction()->getSourceItem()->getBlock();

			if (!isset($this->chosen[$playerName])) {
				$this->chosen[$playerName] = [];
			}

			if (in_array($slot, $this->innerSlot, true) && count($this->chosen[$playerName]) < 5) {
				if (!$chosenWool instanceof Wool) {
					return;
				}

				$this->chosen[$playerName][] = ['color' => $chosenWool->getColor(), 'multiplier' => $table[array_search($slot, $this->innerSlot, true)]];
				$menu->getInventory()->setItem($slot, VanillaBlocks::GLAZED_TERRACOTTA()->setColor($chosenWool->getColor())->asItem());
				$transaction->getPlayer()->getWorld()->addSound($transaction->getPlayer()->getPosition(), new PopSound());

				if (count($this->chosen[$playerName]) === 5) {
					foreach ($inventory->getContents() as $inventorySlot => $item) {
						if ($item->getTypeId() === VanillaBlocks::VINES()->asItem()->getTypeId()) {
							$inventory->setItem($inventorySlot, VanillaBlocks::WEEPING_VINES()->asItem());
						}
					}
				}
			}

			if ($slot === 50) {
				if (count($this->chosen[$playerName]) > 0) {
					$this->revealPrize($transaction->getPlayer(), $bet);
					$transaction->getPlayer()->getWorld()->addSound($transaction->getPlayer()->getPosition(), new XpCollectSound());
				}
			}
		}));

		$menu->send($player);
	}

	private function revealPrize(Player $player, int $bet) : void {
		$economyProvider = Main::getInstance()->getEconomyProvider();
		$menu = InvMenu::create(InvMenu::TYPE_CHEST);
		$menu->setName(Main::getInstance()->getGuiTitle('reveal'));
		$contents = [];

		for ($i = 0; $i <= 26; ++$i) {
			if ($i < 10 || $i > 16 || $i === 15) {
				$contents[$i] = VanillaBlocks::VINES()->asItem();
			}
		}

		/** @var array<float|int> $multipliers */
		$multipliers = [];

		foreach ($this->chosen[$player->getName()] as $key => $value) {
			$contents[$key + 10] = VanillaBlocks::GLAZED_TERRACOTTA()->setColor($value['color'])->asItem();
			$multipliers[$key + 10] = $value['multiplier'];
		}

		[$totalMultiplier, $calculationMessage] = $this->calculateLotteryMultiplier($multipliers);
		$count = count($multipliers);

		$prize = $bet * $totalMultiplier;

		if ($prize < 0) {
			$economyProvider->getMoney($player, function (float|int $amount) use ($economyProvider, $player, $prize) : void {
				if ($amount < abs($prize)) {
					$economyProvider->setMoney($player, 0, function (bool $success) use ($player) : void {
						if (!$success) {
							$player->sendMessage(Main::getInstance()->getMessage('transaction-failed'));
						}
					});
				} else {
					$economyProvider->takeMoney($player, abs($prize), function (bool $success) use ($player) : void {
						if (!$success) {
							$player->sendMessage(Main::getInstance()->getMessage('transaction-failed'));
						}
					});
				}
			});
		} else {
			$economyProvider->giveMoney($player, $prize, function (bool $success) use ($player) : void {
				if (!$success) {
					$player->sendMessage(Main::getInstance()->getMessage('transaction-failed'));
				}
			});
		}

		$menu->getInventory()->setContents($contents);
		$menu->setListener(InvMenu::readonly(function (DeterministicInvMenuTransaction $transaction) use ($menu, $bet, $prize, $multipliers, $totalMultiplier, $count, $calculationMessage) : void {
			$player = $transaction->getPlayer();
			$playerName = $player->getName();
			$slot = $transaction->getAction()->getSlot();
			$sourceItem = $transaction->getAction()->getSourceItem();

			if ($slot >= 10 && $slot <= ($count + 9) && $sourceItem->getTypeId() !== VanillaItems::PAPER()->getTypeId()) {
				$multiplier = $multipliers[$slot];
				unset($this->chosen[$playerName][$slot - 10]);
				$menu->getInventory()->setItem($slot, VanillaItems::PAPER()->setCustomName($this->highlightTextColor($multiplier) . (string) $multiplier . 'x'));
				$player->getWorld()->addSound($player->getPosition(), new XpCollectSound());

				if (count($this->chosen[$playerName]) === 0) {
					unset($this->chosen[$playerName]);
					$menu->getInventory()->setItem(16, VanillaItems::PAPER()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING()))->setCustomName($this->highlightTextColor($prize, $bet) . (string) $prize)->setLore([$calculationMessage . ' = ' . $this->highlightTextColor($totalMultiplier) . (string) $totalMultiplier]));
					$player->getWorld()->addSound($player->getPosition(), new XpLevelUpSound(30));
				}
			}
		}));

		$menu->setInventoryCloseListener(function (Player $player) use ($bet, $prize, $calculationMessage, $totalMultiplier) : void {
			$total = $prize - $bet;
			$player->getServer()->broadcastMessage(str_replace(['{prize}', '{loss}', '{bet}', '{player}', '{calculation}', '{multiplier}'], [(string) $total, (string) $prize, (string) $bet, $player->getName(), $calculationMessage, (string) $totalMultiplier], Main::getInstance()->getMessage('broadcast-message')));

			if ($prize > $bet) {
				$player->sendMessage(str_replace('{prize}', (string) $total, Main::getInstance()->getMessage('receive-prize')));
			} elseif ($total > -$bet && $total < $bet) {
				$player->sendMessage(str_replace('{prize}', (string) $total, Main::getInstance()->getMessage('receive-less-prize')));
			} else {
				$player->sendMessage(str_replace('{prize}', (string) $total, Main::getInstance()->getMessage('loss-prize')));
			}
		});

		$menu->send($player);
	}

	private function highlightTextColor(float|int $multiplier, int $goldCondition = 1) : string {
		return $multiplier >= $goldCondition ? TextFormat::GREEN : ($multiplier > 0 ? TextFormat::GOLD : TextFormat::RED);
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
	private function calculateLotteryMultiplier(array $multipliers, string $option = 'max') : array {
		switch ($option) {
			case 'max':
				$totalMultiplier = max($multipliers);
				$calculationMessage = 'Maximum';
				break;
			case 'min':
				$totalMultiplier = min($multipliers);
				$calculationMessage = 'Minimum';
				break;
			case 'average':
				$totalMultiplier = array_sum($multipliers) / count($multipliers);
				$calculationMessage = 'Average';
				break;
			case 'product':
				$totalMultiplier = array_reduce($multipliers, fn ($last, $current) => $last * $current, 1);
				$calculationMessage = 'Cumulative Multiplication';
				break;
			default:
				throw new InvalidArgumentException('invalid option: ' . $option);
		}

		return [$totalMultiplier, $calculationMessage . '(' . implode(', ', $multipliers) . ')'];
	}

	protected function prepare() : void {
		$this->addConstraint(new InGameRequiredConstraint($this));

		$this->setPermission('lottery.play');
	}
}