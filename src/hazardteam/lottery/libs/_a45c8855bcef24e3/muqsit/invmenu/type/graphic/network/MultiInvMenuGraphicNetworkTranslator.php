<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_a45c8855bcef24e3\muqsit\invmenu\type\graphic\network;

use hazardteam\lottery\libs\_a45c8855bcef24e3\muqsit\invmenu\session\InvMenuInfo;
use hazardteam\lottery\libs\_a45c8855bcef24e3\muqsit\invmenu\session\PlayerSession;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;

final class MultiInvMenuGraphicNetworkTranslator implements InvMenuGraphicNetworkTranslator{

	/**
	 * @param InvMenuGraphicNetworkTranslator[] $translators
	 */
	public function __construct(
		readonly private array $translators
	){}

	public function translate(PlayerSession $session, InvMenuInfo $current, ContainerOpenPacket $packet) : void{
		foreach($this->translators as $translator){
			$translator->translate($session, $current, $packet);
		}
	}
}