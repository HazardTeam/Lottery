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

use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\constraint\InGameRequiredConstraint;
use hazardteam\lottery\Main;
use jojoe77777\FormAPI\CustomForm;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\command\CommandSender;
use pocketmine\inventory\Inventory;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\ItemTypeIds;
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
use function array_search;
use function count;
use function in_array;
use function is_numeric;
use function max;
use function str_replace;

class PlaySubCommand extends BaseSubCommand {
	private array $innerSlot = [];
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
		$chosenColor = [];

		for ($i = 0; $i <= 53; ++$i) {
			if (in_array($i, $this->innerSlot, true)) {
				$color = $colors[array_rand($colors)];
				$contents[$i] = VanillaBlocks::WOOL()->setColor($color)->asItem();
				$chosenColor[array_search($i, $this->innerSlot, true)] = $color;
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
		$menu->setListener(InvMenu::readonly(function (DeterministicInvMenuTransaction $transaction) use ($menu, $bet, $table, $chosenColor) : void {
			$slot = $transaction->getAction()->getSlot();
			$playerName = $transaction->getPlayer()->getName();
			$inventory = $menu->getInventory();

			if (!isset($this->chosen[$playerName])) {
				$this->chosen[$playerName] = [];
			}

			if (in_array($slot, $this->innerSlot, true) && count($this->chosen[$playerName]) < 5) {
				$color = $chosenColor[array_search($slot, $this->innerSlot, true)];
				$this->chosen[$playerName][] = array_search($slot, $this->innerSlot, true);
				$menu->getInventory()->setItem($slot, VanillaBlocks::GLAZED_TERRACOTTA()->setColor($color)->asItem());
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
					$this->revealPrize($transaction->getPlayer(), $bet, $table, $chosenColor);
					$transaction->getPlayer()->getWorld()->addSound($transaction->getPlayer()->getPosition(), new XpCollectSound());
				}
			}
		}));

		$menu->send($player);
	}

	private function revealPrize(Player $player, int $bet, array $table, array $chosenColor) : void {
		$economyProvider = Main::getInstance()->getEconomyProvider();
		$menu = InvMenu::create(InvMenu::TYPE_CHEST);
		$menu->setName(Main::getInstance()->getGuiTitle('reveal'));
		$contents = [];

		for ($i = 0; $i <= 26; ++$i) {
			if ($i < 10 || $i > 16 || $i === 15) {
				$contents[$i] = VanillaBlocks::VINES()->asItem();
			}
		}

		$chosen = [];

		$count = 10;
		foreach ($this->chosen[$player->getName()] as $key => $innerSlot) {
			$color = $chosenColor[$innerSlot];
			$contents[$count] = VanillaBlocks::GLAZED_TERRACOTTA()->setColor($color)->asItem()->setCustomName(Main::getInstance()->getGuiItem('reveal', 'reveal-result'));
			$chosen[$key] = (float) $table[$innerSlot];
			++$count;
		}

		$highest = max($chosen);
		$prize = $bet * $highest;

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
		$menu->setListener(InvMenu::readonly(function (DeterministicInvMenuTransaction $transaction) use ($menu, $bet, $prize, $chosen, $chosenColor) : void {
			$player = $transaction->getPlayer();
			$slot = $transaction->getAction()->getSlot();
			$typeId = VanillaBlocks::GLAZED_TERRACOTTA()->setColor($chosenColor[$slot - 10])->asItem()->getTypeId();

			if ($slot >= 10 && $slot <= (count($chosen) + 9) && $menu->getInventory()->getItem($slot)->getTypeId() === $typeId) {
				$mult = $chosen[$slot - 10];
				$menu->getInventory()->setItem($slot, VanillaItems::PAPER()->setCustomName(($mult >= 1 ? TextFormat::GREEN : ($mult > 0 ? TextFormat::GOLD : TextFormat::RED)) . (string) $mult . 'x'));
				$player->getWorld()->addSound($player->getPosition(), new XpCollectSound());
				$inv = $menu->getInventory();
				$checked = true;

				foreach ($chosen as $key => $value) {
					$item = $inv->getItem((int) $key + 10);
					if ($item->getTypeId() !== ItemTypeIds::PAPER) {
						$checked = false;
						break;
					}
				}

				if ($checked) {
					$menu->getInventory()->setItem(16, VanillaItems::PAPER()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING()))->setCustomName(($prize <= 0 ? TextFormat::RED : ($prize < $bet ? TextFormat::GOLD : TextFormat::GREEN)) . (string) $prize));
					$player->getWorld()->addSound($player->getPosition(), new XpLevelUpSound(30));
				}
			}
		}));

		$menu->setInventoryCloseListener(function (Player $player, Inventory $inventory) use ($bet, $prize) : void {
			unset($this->chosen[$player->getName()]);
			$total = $prize - $bet;
			$player->getServer()->broadcastMessage(str_replace(['{prize}', '{loss}', '{bet}', '{player}'], [(string) $total, (string) $prize, (string) $bet, $player->getName()], Main::getInstance()->getMessage('broadcast-message')));

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

	protected function prepare() : void {
		$this->addConstraint(new InGameRequiredConstraint($this));

		$this->setPermission('lottery.play');
	}
}
