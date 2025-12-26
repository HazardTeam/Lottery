<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_3cee2b378a88b368\muqsit\invmenu\type\graphic;

use pocketmine\math\Vector3;

interface PositionedInvMenuGraphic extends InvMenuGraphic{

	public function getPosition() : Vector3;
}