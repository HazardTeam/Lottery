<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_d1290aa2f9124f2a\muqsit\invmenu\session;

use hazardteam\lottery\libs\_d1290aa2f9124f2a\muqsit\invmenu\InvMenu;
use hazardteam\lottery\libs\_d1290aa2f9124f2a\muqsit\invmenu\type\graphic\InvMenuGraphic;

final class InvMenuInfo{

	public function __construct(
		readonly public InvMenu $menu,
		readonly public InvMenuGraphic $graphic,
		readonly public ?string $graphic_name
	){}
}