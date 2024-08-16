<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_3b0130e2682b4431\muqsit\invmenu\type\graphic;

use pocketmine\math\Vector3;

interface PositionedInvMenuGraphic extends InvMenuGraphic{

	public function getPosition() : Vector3;
}