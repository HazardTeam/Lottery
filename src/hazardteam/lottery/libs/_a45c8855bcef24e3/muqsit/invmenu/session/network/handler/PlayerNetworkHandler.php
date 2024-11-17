<?php

declare(strict_types=1);

namespace hazardteam\lottery\libs\_a45c8855bcef24e3\muqsit\invmenu\session\network\handler;

use Closure;
use hazardteam\lottery\libs\_a45c8855bcef24e3\muqsit\invmenu\session\network\NetworkStackLatencyEntry;

interface PlayerNetworkHandler{

	public function createNetworkStackLatencyEntry(Closure $then) : NetworkStackLatencyEntry;
}