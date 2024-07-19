<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_d1290aa2f9124f2a\muqsit\invmenu\type\graphic;

use pocketmine\math\Vector3;

interface PositionedInvMenuGraphic extends InvMenuGraphic{

	public function getPosition() : Vector3;
}