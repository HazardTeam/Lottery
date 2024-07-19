<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_a289d7e73bad0d48\muqsit\invmenu\type\graphic;

use pocketmine\math\Vector3;

interface PositionedInvMenuGraphic extends InvMenuGraphic{

	public function getPosition() : Vector3;
}