<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_d0d5c1d965246a19\muqsit\invmenu\type;

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