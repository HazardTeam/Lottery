<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_a45c8855bcef24e3\muqsit\invmenu\type\graphic\network;

use hazardteam\lottery\libs\_a45c8855bcef24e3\muqsit\invmenu\session\InvMenuInfo;
use hazardteam\lottery\libs\_a45c8855bcef24e3\muqsit\invmenu\session\PlayerSession;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;

final class WindowTypeInvMenuGraphicNetworkTranslator implements InvMenuGraphicNetworkTranslator{

	public function __construct(
		readonly private int $window_type
	){}

	public function translate(PlayerSession $session, InvMenuInfo $current, ContainerOpenPacket $packet) : void{
		$packet->windowType = $this->window_type;
	}
}