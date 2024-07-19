<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_4e5cd0d2976235cb\muqsit\invmenu\type\graphic;

use pocketmine\math\Vector3;

interface PositionedInvMenuGraphic extends InvMenuGraphic{

	public function getPosition() : Vector3;
}