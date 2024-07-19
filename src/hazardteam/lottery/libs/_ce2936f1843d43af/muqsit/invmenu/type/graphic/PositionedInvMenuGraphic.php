<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_ce2936f1843d43af\muqsit\invmenu\type\graphic;

use pocketmine\math\Vector3;

interface PositionedInvMenuGraphic extends InvMenuGraphic{

	public function getPosition() : Vector3;
}