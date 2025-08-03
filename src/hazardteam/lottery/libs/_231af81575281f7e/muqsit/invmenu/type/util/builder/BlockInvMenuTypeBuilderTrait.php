<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_231af81575281f7e\muqsit\invmenu\type\util\builder;

use LogicException;
use pocketmine\block\Block;

trait BlockInvMenuTypeBuilderTrait{

	private ?Block $block = null;

	public function setBlock(Block $block) : self{
		$this->block = $block;
		return $this;
	}

	protected function getBlock() : Block{
		return $this->block ?? throw new LogicException("No block was provided");
	}
}