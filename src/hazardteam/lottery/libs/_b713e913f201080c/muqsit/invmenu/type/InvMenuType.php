<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_b713e913f201080c\muqsit\invmenu\type;

use hazardteam\lottery\libs\_b713e913f201080c\muqsit\invmenu\InvMenu;
use hazardteam\lottery\libs\_b713e913f201080c\muqsit\invmenu\type\graphic\InvMenuGraphic;
use pocketmine\inventory\Inventory;
use pocketmine\player\Player;

interface InvMenuType{

	public function createGraphic(InvMenu $menu, Player $player) : ?InvMenuGraphic;

	public function createInventory() : Inventory;
}