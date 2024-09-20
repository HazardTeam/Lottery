<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_af0e35b720b1713f\muqsit\invmenu\type\graphic;

use pocketmine\math\Vector3;

interface PositionedInvMenuGraphic extends InvMenuGraphic{

	public function getPosition() : Vector3;
}