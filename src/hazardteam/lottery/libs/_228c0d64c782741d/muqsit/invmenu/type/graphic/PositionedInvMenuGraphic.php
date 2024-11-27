<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_228c0d64c782741d\muqsit\invmenu\type\graphic;

use pocketmine\math\Vector3;

interface PositionedInvMenuGraphic extends InvMenuGraphic{

	public function getPosition() : Vector3;
}