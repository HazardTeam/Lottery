<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_bbbc7c1487197bb3\muqsit\invmenu\type\graphic;

use pocketmine\math\Vector3;

interface PositionedInvMenuGraphic extends InvMenuGraphic{

	public function getPosition() : Vector3;
}