<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_8d027fd3eab1ffb2\muqsit\invmenu\type\graphic;

use pocketmine\math\Vector3;

interface PositionedInvMenuGraphic extends InvMenuGraphic{

	public function getPosition() : Vector3;
}