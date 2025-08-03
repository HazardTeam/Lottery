<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_9a740962d2613bba\muqsit\invmenu\type\graphic;

use pocketmine\math\Vector3;

interface PositionedInvMenuGraphic extends InvMenuGraphic{

	public function getPosition() : Vector3;
}