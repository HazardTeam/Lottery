<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_765c5292a91ead45\muqsit\invmenu\session;

use hazardteam\lottery\libs\_765c5292a91ead45\muqsit\invmenu\InvMenu;
use hazardteam\lottery\libs\_765c5292a91ead45\muqsit\invmenu\type\graphic\InvMenuGraphic;

final class InvMenuInfo{

	public function __construct(
		readonly public InvMenu $menu,
		readonly public InvMenuGraphic $graphic,
		readonly public ?string $graphic_name
	){}
}