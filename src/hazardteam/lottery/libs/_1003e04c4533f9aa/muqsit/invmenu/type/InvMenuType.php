<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_1003e04c4533f9aa\muqsit\invmenu\type;

use hazardteam\lottery\libs\_1003e04c4533f9aa\muqsit\invmenu\InvMenu;
use hazardteam\lottery\libs\_1003e04c4533f9aa\muqsit\invmenu\type\graphic\InvMenuGraphic;
use pocketmine\inventory\Inventory;
use pocketmine\player\Player;

interface InvMenuType{

	public function createGraphic(InvMenu $menu, Player $player) : ?InvMenuGraphic;

	public function createInventory() : Inventory;
}