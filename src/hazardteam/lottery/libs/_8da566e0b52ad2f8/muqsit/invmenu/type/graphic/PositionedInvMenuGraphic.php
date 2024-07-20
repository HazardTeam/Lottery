<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_8da566e0b52ad2f8\muqsit\invmenu\type\graphic;

use pocketmine\math\Vector3;

interface PositionedInvMenuGraphic extends InvMenuGraphic{

	public function getPosition() : Vector3;
}