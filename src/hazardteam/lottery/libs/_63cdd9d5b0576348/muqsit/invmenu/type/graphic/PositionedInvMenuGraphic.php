<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_63cdd9d5b0576348\muqsit\invmenu\type\graphic;

use pocketmine\math\Vector3;

interface PositionedInvMenuGraphic extends InvMenuGraphic{

	public function getPosition() : Vector3;
}