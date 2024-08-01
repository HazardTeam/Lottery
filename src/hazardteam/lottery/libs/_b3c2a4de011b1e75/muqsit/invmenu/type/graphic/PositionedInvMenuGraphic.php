<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_b3c2a4de011b1e75\muqsit\invmenu\type\graphic;

use pocketmine\math\Vector3;

interface PositionedInvMenuGraphic extends InvMenuGraphic{

	public function getPosition() : Vector3;
}