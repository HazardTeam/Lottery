<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_9ac2f8890992e4e5\muqsit\invmenu\type\util\builder;

trait AnimationDurationInvMenuTypeBuilderTrait{

	private int $animation_duration = 0;

	public function setAnimationDuration(int $animation_duration) : self{
		$this->animation_duration = $animation_duration;
		return $this;
	}

	protected function getAnimationDuration() : int{
		return $this->animation_duration;
	}
}