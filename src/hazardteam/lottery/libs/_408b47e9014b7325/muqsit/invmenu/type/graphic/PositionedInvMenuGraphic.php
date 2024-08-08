<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_408b47e9014b7325\muqsit\invmenu\type\graphic;

use pocketmine\math\Vector3;

interface PositionedInvMenuGraphic extends InvMenuGraphic{

	public function getPosition() : Vector3;
}