<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_af0e35b720b1713f\muqsit\invmenu\type\graphic\network;

use hazardteam\lottery\libs\_af0e35b720b1713f\muqsit\invmenu\session\InvMenuInfo;
use hazardteam\lottery\libs\_af0e35b720b1713f\muqsit\invmenu\session\PlayerSession;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;

interface InvMenuGraphicNetworkTranslator{

	public function translate(PlayerSession $session, InvMenuInfo $current, ContainerOpenPacket $packet) : void;
}