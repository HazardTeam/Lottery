<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_3cee2b378a88b368\muqsit\invmenu\type\util\builder;

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