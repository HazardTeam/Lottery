<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_9b5d5eaded1c1208\muqsit\invmenu\type\graphic;

use pocketmine\math\Vector3;

interface PositionedInvMenuGraphic extends InvMenuGraphic{

	public function getPosition() : Vector3;
}