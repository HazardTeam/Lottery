<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_6d19c0889371a630\muqsit\invmenu\type\graphic;

use pocketmine\math\Vector3;

interface PositionedInvMenuGraphic extends InvMenuGraphic{

	public function getPosition() : Vector3;
}