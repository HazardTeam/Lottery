<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_8ea4aa4ff1344ccb\muqsit\invmenu\type\graphic;

use pocketmine\math\Vector3;

interface PositionedInvMenuGraphic extends InvMenuGraphic{

	public function getPosition() : Vector3;
}