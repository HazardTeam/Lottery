<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_4e5cd0d2976235cb\muqsit\invmenu\type\util\builder;

use LogicException;

trait FixedInvMenuTypeBuilderTrait{

	private ?int $size = null;

	public function setSize(int $size) : self{
		$this->size = $size;
		return $this;
	}

	protected function getSize() : int{
		return $this->size ?? throw new LogicException("No size was provided");
	}
}