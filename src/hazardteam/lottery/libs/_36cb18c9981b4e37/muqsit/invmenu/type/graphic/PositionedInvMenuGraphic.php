<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_36cb18c9981b4e37\muqsit\invmenu\type\graphic;

use pocketmine\math\Vector3;

interface PositionedInvMenuGraphic extends InvMenuGraphic{

	public function getPosition() : Vector3;
}