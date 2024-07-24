<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_257307e633e5acba\muqsit\invmenu\type;

use hazardteam\lottery\libs\_257307e633e5acba\muqsit\invmenu\InvMenu;
use hazardteam\lottery\libs\_257307e633e5acba\muqsit\invmenu\type\graphic\InvMenuGraphic;
use pocketmine\inventory\Inventory;
use pocketmine\player\Player;

interface InvMenuType{

	public function createGraphic(InvMenu $menu, Player $player) : ?InvMenuGraphic;

	public function createInventory() : Inventory;
}