<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_ab6a685ecbde31ce\muqsit\invmenu\type\graphic;

use pocketmine\math\Vector3;

interface PositionedInvMenuGraphic extends InvMenuGraphic{

	public function getPosition() : Vector3;
}