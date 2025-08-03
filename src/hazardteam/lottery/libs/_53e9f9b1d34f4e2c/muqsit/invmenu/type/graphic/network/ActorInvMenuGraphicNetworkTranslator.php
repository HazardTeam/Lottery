<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_53e9f9b1d34f4e2c\muqsit\invmenu\type\graphic\network;

use hazardteam\lottery\libs\_53e9f9b1d34f4e2c\muqsit\invmenu\session\InvMenuInfo;
use hazardteam\lottery\libs\_53e9f9b1d34f4e2c\muqsit\invmenu\session\PlayerSession;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;

final class ActorInvMenuGraphicNetworkTranslator implements InvMenuGraphicNetworkTranslator{

	public function __construct(
		readonly private int $actor_runtime_id
	){}

	public function translate(PlayerSession $session, InvMenuInfo $current, ContainerOpenPacket $packet) : void{
		$packet->actorUniqueId = $this->actor_runtime_id;
		$packet->blockPosition = new BlockPosition(0, 0, 0);
	}
}