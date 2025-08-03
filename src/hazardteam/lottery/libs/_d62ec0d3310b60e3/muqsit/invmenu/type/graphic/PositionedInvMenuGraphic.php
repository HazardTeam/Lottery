<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_d62ec0d3310b60e3\muqsit\invmenu\type\graphic;

use pocketmine\math\Vector3;

interface PositionedInvMenuGraphic extends InvMenuGraphic{

	public function getPosition() : Vector3;
}