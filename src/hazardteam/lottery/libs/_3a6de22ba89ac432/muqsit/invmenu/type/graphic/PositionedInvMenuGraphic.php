<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_3a6de22ba89ac432\muqsit\invmenu\type\graphic;

use pocketmine\math\Vector3;

interface PositionedInvMenuGraphic extends InvMenuGraphic{

	public function getPosition() : Vector3;
}