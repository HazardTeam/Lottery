<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_ea3bd7608284511f\muqsit\invmenu\type\graphic\network;

use hazardteam\lottery\libs\_ea3bd7608284511f\muqsit\invmenu\session\InvMenuInfo;
use hazardteam\lottery\libs\_ea3bd7608284511f\muqsit\invmenu\session\PlayerSession;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;

interface InvMenuGraphicNetworkTranslator{

	public function translate(PlayerSession $session, InvMenuInfo $current, ContainerOpenPacket $packet) : void;
}