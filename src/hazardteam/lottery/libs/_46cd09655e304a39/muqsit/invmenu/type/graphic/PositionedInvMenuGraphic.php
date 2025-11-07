<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_46cd09655e304a39\muqsit\invmenu\type\graphic;

use pocketmine\math\Vector3;

interface PositionedInvMenuGraphic extends InvMenuGraphic{

	public function getPosition() : Vector3;
}