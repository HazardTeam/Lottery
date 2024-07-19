<?php

/*
 * Copyright (c) 2024 HazardTeam
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/HazardTeam/Lottery
 */

declare(strict_types=1);

namespace hazardteam\lottery;

use function count;
use function explode;
use function max;
use function mt_rand;
use function strlen;

class LotteryRange {
	public function __construct(
		private string $startRange,
		private string $endRange,
		private int $chance
	) {}

	public function getStartRange() : string {
		return $this->startRange;
	}

	public function getEndRange() : string {
		return $this->endRange;
	}

	public function getChance() : int {
		return $this->chance;
	}

	public function getTable() : array {
		$table = [];
		$startRange = explode('.', $this->startRange);
		$endRange = explode('.', $this->endRange);

		$afterComma = 0;
		if (count($startRange) === 2 && count($endRange) === 2) {
			$afterComma = max(strlen($startRange[1]), strlen($endRange[1]));
		}

		$afterComma = 10 ** $afterComma;

		for ($i = 1; $i <= $this->chance; ++$i) {
			$table[] = mt_rand((int) ((float) $this->startRange * $afterComma), (int) ((float) $this->endRange * $afterComma)) / $afterComma;
		}

		return $table;
	}
}
