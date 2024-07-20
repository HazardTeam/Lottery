<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_7d799c7bec630fa1\muqsit\invmenu\type\graphic;

use pocketmine\math\Vector3;

interface PositionedInvMenuGraphic extends InvMenuGraphic{

	public function getPosition() : Vector3;
}