<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_3165c71a15e0d18e\muqsit\invmenu\type\graphic;

use pocketmine\math\Vector3;

interface PositionedInvMenuGraphic extends InvMenuGraphic{

	public function getPosition() : Vector3;
}