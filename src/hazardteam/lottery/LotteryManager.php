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

use function array_merge;
use function count;
use function mt_rand;
use function shuffle;

class LotteryManager {
	private const MIN_TABLE_SIZE = 28;
	private const MIN_SHUFFLE_COUNT = 5;
	private const MAX_SHUFFLE_COUNT = 10;

	/** @var array<int, LotteryRange> */
	private array $ranges = [];

	/**
	 * @param array<int, array{minRange: string, maxRange: string, chance: int}> $rangeData
	 */
	public function __construct(array $rangeData) {
		foreach ($rangeData as $value) {
			$this->ranges[] = new LotteryRange($value['minRange'], $value['maxRange'], $value['chance']);
		}
	}

	/**
	 * Generate lottery tables with proper shuffling.
	 *
	 * @param array<float> $tables
	 *
	 * @return array<float>
	 */
	public function getTables(array $tables = []) : array {
		foreach ($this->ranges as $range) {
			$tables = array_merge($tables, $range->getTable());
		}

		for ($i = 0; $i < mt_rand(self::MIN_SHUFFLE_COUNT, self::MAX_SHUFFLE_COUNT); ++$i) {
			shuffle($tables);
		}

		if (count($tables) < self::MIN_TABLE_SIZE) {
			return $this->getTables($tables);
		}

		return $tables;
	}
}