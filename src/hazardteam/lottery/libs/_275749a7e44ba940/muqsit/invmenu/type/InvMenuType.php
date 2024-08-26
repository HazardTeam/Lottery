<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_275749a7e44ba940\muqsit\invmenu\type;

use hazardteam\lottery\libs\_275749a7e44ba940\muqsit\invmenu\InvMenu;
use hazardteam\lottery\libs\_275749a7e44ba940\muqsit\invmenu\type\graphic\InvMenuGraphic;
use pocketmine\inventory\Inventory;
use pocketmine\player\Player;

interface InvMenuType{

	public function createGraphic(InvMenu $menu, Player $player) : ?InvMenuGraphic;

	public function createInventory() : Inventory;
}