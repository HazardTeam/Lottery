<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_16d35adbbb7804c1\muqsit\invmenu\type;

use hazardteam\lottery\libs\_16d35adbbb7804c1\muqsit\invmenu\InvMenu;
use hazardteam\lottery\libs\_16d35adbbb7804c1\muqsit\invmenu\type\graphic\InvMenuGraphic;
use pocketmine\inventory\Inventory;
use pocketmine\player\Player;

interface InvMenuType{

	public function createGraphic(InvMenu $menu, Player $player) : ?InvMenuGraphic;

	public function createInventory() : Inventory;
}