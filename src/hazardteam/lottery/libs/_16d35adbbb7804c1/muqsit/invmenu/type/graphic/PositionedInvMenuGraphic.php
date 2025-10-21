<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_16d35adbbb7804c1\muqsit\invmenu\type\graphic;

use pocketmine\math\Vector3;

interface PositionedInvMenuGraphic extends InvMenuGraphic{

	public function getPosition() : Vector3;
}