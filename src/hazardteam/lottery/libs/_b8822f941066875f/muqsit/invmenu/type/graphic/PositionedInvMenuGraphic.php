<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_b8822f941066875f\muqsit\invmenu\type\graphic;

use pocketmine\math\Vector3;

interface PositionedInvMenuGraphic extends InvMenuGraphic{

	public function getPosition() : Vector3;
}