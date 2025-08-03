<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_5fbb0d56cc3dc7c9\muqsit\invmenu\type;

use hazardteam\lottery\libs\_5fbb0d56cc3dc7c9\muqsit\invmenu\InvMenu;
use hazardteam\lottery\libs\_5fbb0d56cc3dc7c9\muqsit\invmenu\type\graphic\InvMenuGraphic;
use pocketmine\inventory\Inventory;
use pocketmine\player\Player;

interface InvMenuType{

	public function createGraphic(InvMenu $menu, Player $player) : ?InvMenuGraphic;

	public function createInventory() : Inventory;
}