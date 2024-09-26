<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_1003e04c4533f9aa\muqsit\invmenu\type\graphic;

use pocketmine\math\Vector3;

interface PositionedInvMenuGraphic extends InvMenuGraphic{

	public function getPosition() : Vector3;
}