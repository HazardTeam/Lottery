<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_96d6c7809a382901\muqsit\invmenu\type\util\builder;

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