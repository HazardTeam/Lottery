<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_53e9f9b1d34f4e2c\muqsit\invmenu\session;

use hazardteam\lottery\libs\_53e9f9b1d34f4e2c\muqsit\invmenu\InvMenu;
use hazardteam\lottery\libs\_53e9f9b1d34f4e2c\muqsit\invmenu\type\graphic\InvMenuGraphic;

final class InvMenuInfo{

	public function __construct(
		readonly public InvMenu $menu,
		readonly public InvMenuGraphic $graphic,
		readonly public ?string $graphic_name
	){}
}