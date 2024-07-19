<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_b8822f941066875f\muqsit\invmenu\session;

use hazardteam\lottery\libs\_b8822f941066875f\muqsit\invmenu\InvMenu;
use hazardteam\lottery\libs\_b8822f941066875f\muqsit\invmenu\type\graphic\InvMenuGraphic;

final class InvMenuInfo{

	public function __construct(
		readonly public InvMenu $menu,
		readonly public InvMenuGraphic $graphic,
		readonly public ?string $graphic_name
	){}
}