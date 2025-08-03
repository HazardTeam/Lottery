<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_f5f0db04d11d46cc\muqsit\invmenu\type\graphic\network;

use hazardteam\lottery\libs\_f5f0db04d11d46cc\muqsit\invmenu\session\InvMenuInfo;
use hazardteam\lottery\libs\_f5f0db04d11d46cc\muqsit\invmenu\session\PlayerSession;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;

interface InvMenuGraphicNetworkTranslator{

	public function translate(PlayerSession $session, InvMenuInfo $current, ContainerOpenPacket $packet) : void;
}