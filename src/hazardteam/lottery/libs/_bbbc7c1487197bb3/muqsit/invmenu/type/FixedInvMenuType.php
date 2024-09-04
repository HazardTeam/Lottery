<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_bbbc7c1487197bb3\muqsit\invmenu\type;

/**
 * An InvMenuType with a fixed inventory size.
 */
interface FixedInvMenuType extends InvMenuType{

	/**
	 * Returns size (number of slots) of the inventory.
	 *
	 * @return int
	 */
	public function getSize() : int;
}