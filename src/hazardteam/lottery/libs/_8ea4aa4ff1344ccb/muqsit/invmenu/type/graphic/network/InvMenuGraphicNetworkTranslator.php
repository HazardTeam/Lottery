<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_8ea4aa4ff1344ccb\muqsit\invmenu\type\graphic\network;

use hazardteam\lottery\libs\_8ea4aa4ff1344ccb\muqsit\invmenu\session\InvMenuInfo;
use hazardteam\lottery\libs\_8ea4aa4ff1344ccb\muqsit\invmenu\session\PlayerSession;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;

interface InvMenuGraphicNetworkTranslator{

	public function translate(PlayerSession $session, InvMenuInfo $current, ContainerOpenPacket $packet) : void;
}