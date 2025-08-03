<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_83686bfef791a97b\muqsit\invmenu\type\graphic\network;

use hazardteam\lottery\libs\_83686bfef791a97b\muqsit\invmenu\session\InvMenuInfo;
use hazardteam\lottery\libs\_83686bfef791a97b\muqsit\invmenu\session\PlayerSession;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;

interface InvMenuGraphicNetworkTranslator{

	public function translate(PlayerSession $session, InvMenuInfo $current, ContainerOpenPacket $packet) : void;
}