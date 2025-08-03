<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_a155fcbf6eaf9375\muqsit\invmenu\type\graphic;

use pocketmine\math\Vector3;

interface PositionedInvMenuGraphic extends InvMenuGraphic{

	public function getPosition() : Vector3;
}