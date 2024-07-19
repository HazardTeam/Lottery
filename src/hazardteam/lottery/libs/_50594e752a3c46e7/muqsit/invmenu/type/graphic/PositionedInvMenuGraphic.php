<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_50594e752a3c46e7\muqsit\invmenu\type\graphic;

use pocketmine\math\Vector3;

interface PositionedInvMenuGraphic extends InvMenuGraphic{

	public function getPosition() : Vector3;
}