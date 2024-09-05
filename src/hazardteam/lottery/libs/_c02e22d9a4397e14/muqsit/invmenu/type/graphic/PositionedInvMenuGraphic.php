<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_c02e22d9a4397e14\muqsit\invmenu\type\graphic;

use pocketmine\math\Vector3;

interface PositionedInvMenuGraphic extends InvMenuGraphic{

	public function getPosition() : Vector3;
}