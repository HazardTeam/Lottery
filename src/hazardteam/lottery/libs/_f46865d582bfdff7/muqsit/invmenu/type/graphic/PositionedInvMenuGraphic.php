<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_f46865d582bfdff7\muqsit\invmenu\type\graphic;

use pocketmine\math\Vector3;

interface PositionedInvMenuGraphic extends InvMenuGraphic{

	public function getPosition() : Vector3;
}