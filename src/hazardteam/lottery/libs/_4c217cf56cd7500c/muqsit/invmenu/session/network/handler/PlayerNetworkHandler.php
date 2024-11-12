<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_4c217cf56cd7500c\muqsit\invmenu\session\network\handler;

use Closure;
use hazardteam\lottery\libs\_4c217cf56cd7500c\muqsit\invmenu\session\network\NetworkStackLatencyEntry;

interface PlayerNetworkHandler{

	public function createNetworkStackLatencyEntry(Closure $then) : NetworkStackLatencyEntry;
}