<?php

namespace hazardteam\lottery\commands\subcommands;

use CortexPE\Commando\BaseSubCommand;
use jojoe77777\FormAPI\CustomForm;
use hazardteam\lottery\Main;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use onebone\economyapi\EconomyAPI;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\inventory\Inventory;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\sound\PopSound;
use pocketmine\world\sound\XpCollectSound;
use pocketmine\world\sound\XpLevelUpSound;

class PlaySubCommand extends BaseSubCommand {

    private $offset = [10, 11, 12, 13, 14, 15, 16, 19, 20, 21, 22, 23, 24, 25, 28, 29, 30, 31, 32, 33, 34, 37, 38, 39, 40, 41, 42, 43];
    private $choosed = [];
    
	/**
	 * @param \pocketmine\command\CommandSender $sender
	 * @param string $aliasUsed
	 * @param array $args
	 */
	public function onRun(\pocketmine\command\CommandSender $sender, string $aliasUsed, array $args): void {
        if(!$sender instanceof Player){
            $sender->sendMessage("Please use this ingames");
            return;
        }

        $this->PlayMenu($sender);
	}

    public function PlayMenu(Player $player){
        $form = new CustomForm(function(Player $player, $data = null){
            if($data === null){
                return;
            }

            if(!is_numeric($data["bet"])){
                $player->sendMessage(Main::getInstance()->getConfig()->getNested("messages.invalid-bet"));
                return;
            }

            if(intval($data["bet"]) < intval(Main::getInstance()->getConfig()->getNested("min-bet"))){
                $player->sendMessage(Main::getInstance()->getConfig()->getNested("messages.less-that-min-bet"));
                return;
            }

            if(EconomyAPI::getInstance()->myMoney($player) < intval($data["bet"])){
                $player->sendMessage(Main::getInstance()->getConfig()->getNested("messages.no-enough-money"));
                return;
            }

            EconomyAPI::getInstance()->reduceMoney($player, intval($data["bet"]), true, "Lottery");
            $pk = PlaySoundPacket::create("ambient.cave", $player->getPosition()->getX(), $player->getPosition()->getY(), $player->getPosition()->getZ(), 185.0, 1);
            $player->getNetworkSession()->sendDataPacket($pk, true);
            $this->LotteryMenu($player, intval($data["bet"]));
        });

        $form->setTitle(Main::getInstance()->getConfig()->getNested("forms.play.title"));
        $form->addLabel(str_replace("{money}", strval(EconomyAPI::getInstance()->myMoney($player)), Main::getInstance()->getConfig()->getNested("forms.play.content")));
        $form->addInput("§6» §fPasang Taruhanmu:", default: strval(Main::getInstance()->getConfig()->getNested("min-bet")), label: "bet");
        $player->sendForm($form);
    }

    public function LotteryMenu(Player $player, int $bet){
        $table = Main::getInstance()->getLotteryManager()->getTables();
        $colors = [DyeColor::RED(), DyeColor::GREEN(), DyeColor::CYAN(), DyeColor::ORANGE(), DyeColor::LIGHT_BLUE(), DyeColor::LIME()];
        $contents = [];
        for ($i = 0; $i <= 53; $i++){
            if(in_array($i, $this->offset)){
                $contents[$i] = VanillaBlocks::WOOL()->setColor($colors[array_rand($colors)])->asItem();
            } elseif($i == 48){
                $contents[$i] = VanillaItems::BOOK()->setCustomName(str_replace("{bet}", strval($bet), Main::getInstance()->getConfig()->getNested("gui.lottery.items.bet-info")));
            } elseif($i == 50){
                $contents[$i] = VanillaItems::GOLD_INGOT()->setCustomName(Main::getInstance()->getConfig()->getNested("gui.lottery.items.reveal"));
            } else {
                $contents[$i] = VanillaBlocks::IRON_BARS()->asItem();
            }
        }

        $menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
        $menu->setName(Main::getInstance()->getConfig()->getNested("gui.lottery.title"));
        $menu->getInventory()->setContents($contents);
        $menu->setListener(InvMenu::readonly(function(DeterministicInvMenuTransaction $transaction) use ($menu, $bet, $table) : void{
            $slot = $transaction->getAction()->getSlot();

            if(!isset($this->choosed[$transaction->getPlayer()->getName()])){
            	$this->choosed[$transaction->getPlayer()->getName()] = [];
            }

            if(in_array($slot, $this->offset) && count($this->choosed[$transaction->getPlayer()->getName()]) < 5){
                $this->choosed[$transaction->getPlayer()->getName()][] = array_search($slot, $this->offset);
                $menu->getInventory()->setItem($slot, VanillaBlocks::STONE()->asItem());
                $transaction->getPlayer()->getWorld()->addSound($transaction->getPlayer()->getPosition(), new PopSound());
            }

            if($slot == 50){
                //$transaction->getPlayer()->removeCurrentWindow();
                if(count($this->choosed[$transaction->getPlayer()->getName()]) > 0) {
                	$this->RevealPrice($transaction->getPlayer(), $bet, $table);
                	$transaction->getPlayer()->getWorld()->addSound($transaction->getPlayer()->getPosition(), new XpCollectSound());
                }
            }
        }));

        $menu->send($player);
    }

    public function RevealPrice(Player $player, int $bet, array $table){
        $menu = InvMenu::create(InvMenu::TYPE_CHEST);
        $menu->setName(Main::getInstance()->getConfig()->getNested("gui.reveal.title"));
        $contents = [];
        for($i = 0; $i <= 26; $i++){
            if($i < 10 || $i > 16 || $i == 15){
                $contents[$i] = VanillaBlocks::IRON_BARS()->asItem();
            }
        }

        $choosed = [];

        $count = 10;
        foreach($this->choosed[$player->getName()] as $key => $offset){
            $contents[$count] = VanillaBlocks::STONE()->asItem()->setCustomName(Main::getInstance()->getConfig()->getNested("gui.reveal.items.reveal-result"));
            $choosed[$key] = floatval($table[$offset]);
            $count++;
        }
        $highest = max($choosed);
        $price = $bet * $highest;

        if($price < 0){
            if(EconomyAPI::getInstance()->myMoney($player) < abs($price)){
                EconomyAPI::getInstance()->setMoney($player, 0, true, "Lottery");
            } else {
                EconomyAPI::getInstance()->reduceMoney($player, abs($price), true, "Lottery");
            }
        } else {
            EconomyAPI::getInstance()->addMoney($player, $price, true, "Lottery");
        }

        $menu->getInventory()->setContents($contents);
        $menu->setListener(InvMenu::readonly(function (DeterministicInvMenuTransaction $transaction) use ($menu, $bet, $table, $price, $choosed): void {
            $player = $transaction->getPlayer();
            $slot = $transaction->getAction()->getSlot();
            if($slot >= 10 && $slot <= (count($choosed) + 9) && $menu->getInventory()->getItem($slot)->getTypeId() == VanillaBlocks::STONE()->asItem()->getTypeId()){
                $mult = $choosed[$slot - 10];
                $menu->getInventory()->setItem($slot, VanillaItems::PAPER()->setCustomName(($mult >= 1 ? TextFormat::GREEN : ($mult > 0 ? TextFormat::GOLD : TextFormat::RED)) . strval($mult) . "x"));
                $player->getWorld()->addSound($player->getPosition(), new XpCollectSound());
                $inv = $menu->getInventory();
                $checked = false;
                foreach ($choosed as $key => $value) {
                	if($inv->getItem($key + 10)->getTypeId() == ItemTypeIds::PAPER){
                		$checked = true;
                	} else {
                		$checked = false;
                		break;
                	}
                }
                if($checked){
                    $menu->getInventory()->setItem(16, VanillaItems::PAPER()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING()))->setCustomName(($price <= 0 ? TextFormat::RED : ($price < $bet ? TextFormat::GOLD : TextFormat::GREEN)) . strval($price)));
                    $player->getWorld()->addSound($player->getPosition(), new XpLevelUpSound(30));
                }
            }
        }));
        $menu->setInventoryCloseListener(function(Player $player, Inventory $inventory) use ($bet, $table, $price) : void{
        	unset($this->choosed[$player->getName()]);
            $total = $price - $bet;
            $player->getServer()->broadcastMessage(str_replace(["{price}", "{loss}", "{bet}", "{player}"], [strval($total), strval($price), strval($bet), $player->getName()], Main::getInstance()->getConfig()->getNested("messages.broadcast-message")));
            if($price > $bet){
                $player->sendMessage(str_replace("{price}", strval($total), Main::getInstance()->getConfig()->getNested("messages.receive-price")));
                return;
            } else {
                if($total > -($bet) && $total < $bet){
                    $player->sendMessage(str_replace("{price}", strval($total), Main::getInstance()->getConfig()->getNested("messages.receive-less-price")));
                    return;
                }
                $player->sendMessage(str_replace("{price}", strval($total), Main::getInstance()->getConfig()->getNested("messages.loss-price")));
            }
        });
        $menu->send($player);
    }
	
	/**
	 * This is where all the arguments, permissions, sub-commands, etc would be registered
	 */
	protected function prepare(): void {
        $this->setPermission("lottery.play");
	}
}