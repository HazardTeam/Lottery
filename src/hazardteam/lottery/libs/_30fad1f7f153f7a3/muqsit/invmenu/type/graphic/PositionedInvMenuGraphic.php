<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_30fad1f7f153f7a3\muqsit\invmenu\type\graphic;

use pocketmine\math\Vector3;

interface PositionedInvMenuGraphic extends InvMenuGraphic{

	public function getPosition() : Vector3;
}