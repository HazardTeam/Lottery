<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_16d35adbbb7804c1\muqsit\invmenu\type\util\builder;

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