<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_0401bd3b0acafac5\muqsit\invmenu\type\graphic;

use pocketmine\math\Vector3;

interface PositionedInvMenuGraphic extends InvMenuGraphic{

	public function getPosition() : Vector3;
}