<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_97cb8c5932b18c75\muqsit\invmenu\type\graphic;

use pocketmine\math\Vector3;

interface PositionedInvMenuGraphic extends InvMenuGraphic{

	public function getPosition() : Vector3;
}