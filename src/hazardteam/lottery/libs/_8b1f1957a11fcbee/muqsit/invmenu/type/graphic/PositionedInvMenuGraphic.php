<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_8b1f1957a11fcbee\muqsit\invmenu\type\graphic;

use pocketmine\math\Vector3;

interface PositionedInvMenuGraphic extends InvMenuGraphic{

	public function getPosition() : Vector3;
}