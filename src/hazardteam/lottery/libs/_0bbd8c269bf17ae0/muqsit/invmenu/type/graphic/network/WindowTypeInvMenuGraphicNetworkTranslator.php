<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_0bbd8c269bf17ae0\muqsit\invmenu\type\graphic\network;

use hazardteam\lottery\libs\_0bbd8c269bf17ae0\muqsit\invmenu\session\InvMenuInfo;
use hazardteam\lottery\libs\_0bbd8c269bf17ae0\muqsit\invmenu\session\PlayerSession;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;

final class WindowTypeInvMenuGraphicNetworkTranslator implements InvMenuGraphicNetworkTranslator{

	public function __construct(
		readonly private int $window_type
	){}

	public function translate(PlayerSession $session, InvMenuInfo $current, ContainerOpenPacket $packet) : void{
		$packet->windowType = $this->window_type;
	}
}