<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_ab6a685ecbde31ce\muqsit\invmenu\session\network\handler;

use Closure;
use hazardteam\lottery\libs\_ab6a685ecbde31ce\muqsit\invmenu\session\network\NetworkStackLatencyEntry;

interface PlayerNetworkHandler{

	public function createNetworkStackLatencyEntry(Closure $then) : NetworkStackLatencyEntry;
}