<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_22bcecf331b7b6cf\muqsit\invmenu\type\graphic;

use pocketmine\math\Vector3;

interface PositionedInvMenuGraphic extends InvMenuGraphic{

	public function getPosition() : Vector3;
}