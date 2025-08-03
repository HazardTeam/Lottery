<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_14e1dbb8e0ddfaa5\muqsit\invmenu\type\graphic\network;

use InvalidArgumentException;
use hazardteam\lottery\libs\_14e1dbb8e0ddfaa5\muqsit\invmenu\session\InvMenuInfo;
use hazardteam\lottery\libs\_14e1dbb8e0ddfaa5\muqsit\invmenu\session\PlayerSession;
use hazardteam\lottery\libs\_14e1dbb8e0ddfaa5\muqsit\invmenu\type\graphic\PositionedInvMenuGraphic;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;

final class BlockInvMenuGraphicNetworkTranslator implements InvMenuGraphicNetworkTranslator{

	public static function instance() : self{
		static $instance = null;
		return $instance ??= new self();
	}

	private function __construct(){
	}

	public function translate(PlayerSession $session, InvMenuInfo $current, ContainerOpenPacket $packet) : void{
		$graphic = $current->graphic;
		$graphic instanceof PositionedInvMenuGraphic || throw new InvalidArgumentException("Expected " . PositionedInvMenuGraphic::class . ", got " . $graphic::class);
		$pos = $graphic->getPosition();
		$packet->blockPosition = new BlockPosition((int) $pos->x, (int) $pos->y, (int) $pos->z);
	}
}