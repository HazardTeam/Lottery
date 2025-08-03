<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_53e9f9b1d34f4e2c\muqsit\invmenu\type\graphic;

use pocketmine\math\Vector3;

interface PositionedInvMenuGraphic extends InvMenuGraphic{

	public function getPosition() : Vector3;
}