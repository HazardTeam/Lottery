<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_19b4b574ae481b44\muqsit\invmenu\type;

use hazardteam\lottery\libs\_19b4b574ae481b44\muqsit\invmenu\InvMenu;
use hazardteam\lottery\libs\_19b4b574ae481b44\muqsit\invmenu\type\graphic\InvMenuGraphic;
use pocketmine\inventory\Inventory;
use pocketmine\player\Player;

interface InvMenuType{

	public function createGraphic(InvMenu $menu, Player $player) : ?InvMenuGraphic;

	public function createInventory() : Inventory;
}