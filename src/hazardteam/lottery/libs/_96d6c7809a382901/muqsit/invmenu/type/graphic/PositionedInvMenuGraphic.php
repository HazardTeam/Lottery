<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_96d6c7809a382901\muqsit\invmenu\type\graphic;

use pocketmine\math\Vector3;

interface PositionedInvMenuGraphic extends InvMenuGraphic{

	public function getPosition() : Vector3;
}