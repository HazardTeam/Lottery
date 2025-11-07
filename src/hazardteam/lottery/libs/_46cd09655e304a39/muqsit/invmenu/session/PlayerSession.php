<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_46cd09655e304a39\muqsit\invmenu\session;

use hazardteam\lottery\libs\_46cd09655e304a39\muqsit\invmenu\session\network\PlayerNetwork;
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
		$this->network->finalize();
		$this->dispatcher?->finalize(); // dispatcher finalized first, it has authority to nullify current
		$this->dispatcher = null;
		if($this->current !== null){
			$this->current->graphic->remove($this->player);
			$this->player->removeCurrentWindow();
			$this->current = null;
		}
	}
}