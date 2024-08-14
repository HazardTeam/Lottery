<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_19b4b574ae481b44\muqsit\invmenu\type\graphic;

use pocketmine\math\Vector3;

interface PositionedInvMenuGraphic extends InvMenuGraphic{

	public function getPosition() : Vector3;
}