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

use hazardteam\lottery\libs\_04f5eaeec30870c6\CortexPE\Commando\BaseSubCommand;
use hazardteam\lottery\libs\_04f5eaeec30870c6\CortexPE\Commando\constraint\InGameRequiredConstraint;
use hazardteam\lottery\Main;
use hazardteam\lottery\libs\_04f5eaeec30870c6\jojoe77777\FormAPI\CustomForm;
use hazardteam\lottery\libs\_04f5eaeec30870c6\muqsit\invmenu\InvMenu;
use hazardteam\lottery\libs\_04f5eaeec30870c6\muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use onebone\economyapi\EconomyAPI;
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
		$form = new CustomForm(function (Player $player, $data = null) : void {
			if ($data === null) {
				return;
			}

			if (!is_numeric($data['bet'])) {
				$player->sendMessage(Main::getInstance()->getConfig()->getNested('messages.invalid-bet'));
				return;
			}

			$bet = (int) $data['bet'];
			$minBet = (int) Main::getInstance()->getConfig()->getNested('min-bet', 1000);

			if ($bet < $minBet) {
				$player->sendMessage(Main::getInstance()->getConfig()->getNested('messages.less-than-min-bet'));
				return;
			}

			if (EconomyAPI::getInstance()->myMoney($player) < $bet) {
				$player->sendMessage(Main::getInstance()->getConfig()->getNested('messages.not-enough-money'));
				return;
			}

			EconomyAPI::getInstance()->reduceMoney($player, $bet, true, 'Lottery');
			$pk = PlaySoundPacket::create('ambient.cave', $player->getPosition()->getX(), $player->getPosition()->getY(), $player->getPosition()->getZ(), 185.0, 1);
			$player->getNetworkSession()->sendDataPacket($pk, true);
			$this->showLotteryMenu($player, $bet);
		});

		$form->setTitle(Main::getInstance()->getConfig()->getNested('forms.play.title'));
		$form->addLabel(str_replace('{money}', (string) EconomyAPI::getInstance()->myMoney($player), Main::getInstance()->getConfig()->getNested('forms.play.content')));
		$form->addInput('§6» §fPlace your bet:', default: (string) (Main::getInstance()->getConfig()->getNested('min-bet', 1000)), label: 'bet');
		$player->sendForm($form);
	}

	private function showLotteryMenu(Player $player, int $bet) : void {
		$table = Main::getInstance()->getLotteryManager()->getTables();
		$colors = [DyeColor::RED(), DyeColor::GREEN(), DyeColor::CYAN(), DyeColor::ORANGE(), DyeColor::LIGHT_BLUE(), DyeColor::LIME()];
		$contents = [];

		for ($i = 0; $i <= 53; ++$i) {
			if (in_array($i, $this->innerSlot, true)) {
				$contents[$i] = VanillaBlocks::WOOL()->setColor($colors[array_rand($colors)])->asItem();
			} elseif ($i === 48) {
				$contents[$i] = VanillaItems::BOOK()->setCustomName(str_replace('{bet}', (string) $bet, Main::getInstance()->getConfig()->getNested('gui.lottery.items.bet-info', '§eYour Bet: §b §a{bet}')));
			} elseif ($i === 50) {
				$contents[$i] = VanillaItems::GOLD_INGOT()->setCustomName(Main::getInstance()->getConfig()->getNested('gui.lottery.items.reveal', '§aPreview Result'));
			} else {
				$contents[$i] = VanillaBlocks::IRON_BARS()->asItem();
			}
		}

		$menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
		$menu->setName((string) Main::getInstance()->getConfig()->getNested('gui.lottery.title', ''));
		$menu->getInventory()->setContents($contents);
		$menu->setListener(InvMenu::readonly(function (DeterministicInvMenuTransaction $transaction) use ($menu, $bet, $table) : void {
			$slot = $transaction->getAction()->getSlot();
			$playerName = $transaction->getPlayer()->getName();

			if (!isset($this->chosen[$playerName])) {
				$this->chosen[$playerName] = [];
			}

			if (in_array($slot, $this->innerSlot, true) && count($this->chosen[$playerName]) < 5) {
				$this->chosen[$playerName][] = array_search($slot, $this->innerSlot, true);
				$menu->getInventory()->setItem($slot, VanillaBlocks::STONE()->asItem());
				$transaction->getPlayer()->getWorld()->addSound($transaction->getPlayer()->getPosition(), new PopSound());
			}

			if ($slot === 50) {
				if (count($this->chosen[$playerName]) > 0) {
					$this->revealPrize($transaction->getPlayer(), $bet, $table);
					$transaction->getPlayer()->getWorld()->addSound($transaction->getPlayer()->getPosition(), new XpCollectSound());
				}
			}
		}));

		$menu->send($player);
	}

	private function revealPrize(Player $player, int $bet, array $table) : void {
		$menu = InvMenu::create(InvMenu::TYPE_CHEST);
		$menu->setName((string) Main::getInstance()->getConfig()->getNested('gui.reveal.title'));
		$contents = [];

		for ($i = 0; $i <= 26; ++$i) {
			if ($i < 10 || $i > 16 || $i === 15) {
				$contents[$i] = VanillaBlocks::IRON_BARS()->asItem();
			}
		}

		$chosen = [];

		$count = 10;
		foreach ($this->chosen[$player->getName()] as $key => $innerSlot) {
			$contents[$count] = VanillaBlocks::STONE()->asItem()->setCustomName((string) Main::getInstance()->getConfig()->getNested('gui.reveal.items.reveal-result'));
			$chosen[$key] = (float) $table[$innerSlot];
			++$count;
		}

		$highest = max($chosen);
		$prize = $bet * $highest;

		if ($prize < 0) {
			if (EconomyAPI::getInstance()->myMoney($player) < abs($prize)) {
				EconomyAPI::getInstance()->setMoney($player, 0, true, 'Lottery');
			} else {
				EconomyAPI::getInstance()->reduceMoney($player, abs($prize), true, 'Lottery');
			}
		} else {
			EconomyAPI::getInstance()->addMoney($player, $prize, true, 'Lottery');
		}

		$menu->getInventory()->setContents($contents);
		$menu->setListener(InvMenu::readonly(function (DeterministicInvMenuTransaction $transaction) use ($menu, $bet, $prize, $chosen) : void {
			$player = $transaction->getPlayer();
			$slot = $transaction->getAction()->getSlot();
			$typeId = VanillaBlocks::STONE()->asItem()->getTypeId();

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
			$player->getServer()->broadcastMessage(str_replace(['{prize}', '{loss}', '{bet}', '{player}'], [(string) $total, (string) $prize, (string) $bet, $player->getName()], Main::getInstance()->getConfig()->getNested('messages.broadcast-message')));

			if ($prize > $bet) {
				$player->sendMessage(str_replace('{prize}', (string) $total, Main::getInstance()->getConfig()->getNested('messages.receive-prize')));
			} elseif ($total > -$bet && $total < $bet) {
				$player->sendMessage(str_replace('{prize}', (string) $total, Main::getInstance()->getConfig()->getNested('messages.receive-less-prize')));
			} else {
				$player->sendMessage(str_replace('{prize}', (string) $total, Main::getInstance()->getConfig()->getNested('messages.loss-prize')));
			}
		});

		$menu->send($player);
	}

	protected function prepare() : void {
		$this->addConstraint(new InGameRequiredConstraint($this));

		$this->setPermission('lottery.play');
	}
}