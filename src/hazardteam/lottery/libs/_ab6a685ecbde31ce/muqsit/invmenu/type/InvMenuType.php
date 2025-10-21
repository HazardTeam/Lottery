<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_ab6a685ecbde31ce\muqsit\invmenu\type;

use hazardteam\lottery\libs\_ab6a685ecbde31ce\muqsit\invmenu\InvMenu;
use hazardteam\lottery\libs\_ab6a685ecbde31ce\muqsit\invmenu\type\graphic\InvMenuGraphic;
use pocketmine\inventory\Inventory;
use pocketmine\player\Player;

interface InvMenuType{

	public function createGraphic(InvMenu $menu, Player $player) : ?InvMenuGraphic;

	public function createInventory() : Inventory;
}