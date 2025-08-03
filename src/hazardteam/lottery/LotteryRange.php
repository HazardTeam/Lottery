<?php

/*
 * Copyright (c) 2024-2025 HazardTeam
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
		private readonly string $startRange,
		private readonly string $endRange,
		private readonly int $chance
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

	/**
	 * Generates a table of random float values within the specified range,
	 * repeated 'chance' number of times.
	 *
	 * @return array<float>
	 */
	public function getTable() : array {
		$table = [];
		$startParts = explode('.', $this->startRange);
		$endParts = explode('.', $this->endRange);

		$afterComma = 0;
		if (count($startParts) === 2 && count($endParts) === 2) {
			$afterComma = max(strlen($startParts[1]), strlen($endParts[1]));
		}

		$precisionFactor = 10 ** $afterComma;

		$minVal = (int) ((float) $this->startRange * $precisionFactor);
		$maxVal = (int) ((float) $this->endRange * $precisionFactor);

		if ($minVal > $maxVal) {
			[$minVal, $maxVal] = [$maxVal, $minVal];
		}

		for ($i = 1; $i <= $this->chance; ++$i) {
			$table[] = mt_rand($minVal, $maxVal) / $precisionFactor;
		}

		return $table;
	}
}