<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_61b4ca28e549e95d\muqsit\invmenu\type\graphic;

use pocketmine\math\Vector3;

interface PositionedInvMenuGraphic extends InvMenuGraphic{

	public function getPosition() : Vector3;
}