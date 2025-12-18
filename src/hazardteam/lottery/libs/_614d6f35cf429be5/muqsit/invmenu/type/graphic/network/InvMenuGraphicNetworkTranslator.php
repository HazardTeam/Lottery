<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_614d6f35cf429be5\muqsit\invmenu\type\graphic\network;

use hazardteam\lottery\libs\_614d6f35cf429be5\muqsit\invmenu\session\InvMenuInfo;
use hazardteam\lottery\libs\_614d6f35cf429be5\muqsit\invmenu\session\PlayerSession;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;

interface InvMenuGraphicNetworkTranslator{

	public function translate(PlayerSession $session, InvMenuInfo $current, ContainerOpenPacket $packet) : void;
}