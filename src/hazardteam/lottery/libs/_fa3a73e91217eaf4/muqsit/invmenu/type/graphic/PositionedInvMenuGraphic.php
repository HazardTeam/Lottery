<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_fa3a73e91217eaf4\muqsit\invmenu\type\graphic;

use pocketmine\math\Vector3;

interface PositionedInvMenuGraphic extends InvMenuGraphic{

	public function getPosition() : Vector3;
}