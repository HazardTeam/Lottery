<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_53e9f9b1d34f4e2c\muqsit\invmenu\type;

use hazardteam\lottery\libs\_53e9f9b1d34f4e2c\muqsit\invmenu\InvMenu;
use hazardteam\lottery\libs\_53e9f9b1d34f4e2c\muqsit\invmenu\type\graphic\InvMenuGraphic;
use pocketmine\inventory\Inventory;
use pocketmine\player\Player;

interface InvMenuType{

	public function createGraphic(InvMenu $menu, Player $player) : ?InvMenuGraphic;

	public function createInventory() : Inventory;
}