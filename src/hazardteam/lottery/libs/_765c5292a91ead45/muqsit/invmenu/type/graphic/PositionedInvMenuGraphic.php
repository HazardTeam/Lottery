<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_765c5292a91ead45\muqsit\invmenu\type\graphic;

use pocketmine\math\Vector3;

interface PositionedInvMenuGraphic extends InvMenuGraphic{

	public function getPosition() : Vector3;
}