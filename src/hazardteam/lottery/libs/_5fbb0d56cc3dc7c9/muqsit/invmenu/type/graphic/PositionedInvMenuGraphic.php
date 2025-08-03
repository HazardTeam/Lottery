<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_5fbb0d56cc3dc7c9\muqsit\invmenu\type\graphic;

use pocketmine\math\Vector3;

interface PositionedInvMenuGraphic extends InvMenuGraphic{

	public function getPosition() : Vector3;
}