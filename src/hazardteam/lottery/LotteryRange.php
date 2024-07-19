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

use function mt_rand;

class LotteryRange {
	public function __construct(
		private float|int $startRange,
		private float|int $endRange,
		private int $chance
	) {}

	public function getStartRange() : float|int {
		return $this->startRange;
	}

	public function getEndRange() : float|int {
		return $this->endRange;
	}

	public function getChance() : int {
		return $this->chance;
	}

	public function getTable() : array {
		$table = [];
		for ($i = 1; $i <= $this->chance; ++$i) {
			$table[] = mt_rand($this->startRange * 100, $this->endRange * 100) / 100;
		}

		return $table;
	}
}