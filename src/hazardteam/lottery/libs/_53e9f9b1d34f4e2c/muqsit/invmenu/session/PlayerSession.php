<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_53e9f9b1d34f4e2c\muqsit\invmenu\session;

use hazardteam\lottery\libs\_53e9f9b1d34f4e2c\muqsit\invmenu\session\network\PlayerNetwork;
use pocketmine\player\Player;

final class PlayerSession{

	public ?PlayerWindowDispatcher $dispatcher = null;
	public ?InvMenuInfo $current = null;

	public function __construct(
		readonly public Player $player,
		readonly public PlayerNetwork $network
	){}

	/**
	 * @internal
	 */
	public function finalize() : void{
		if($this->current !== null){
			$this->current->graphic->remove($this->player);
			$this->player->removeCurrentWindow();
		}
		$this->network->finalize();
		$this->dispatcher?->finalize();
		$this->dispatcher = null;
	}
}