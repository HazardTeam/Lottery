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

use UnexpectedValueException;
use function array_merge;
use function count;
use function explode;
use function is_array;
use function mt_rand;
use function shuffle;

class LotteryManager {
	/**
	 * Summary of ranges.
	 *
	 * @var array<LotteryRange>
	 */
	private array $ranges = [];

	public function __construct() {
		$config = Main::getInstance()->getConfig();
		/** @var string $range */
		$range = $config->get('range');

		if (!is_array($range)) {
			throw new UnexpectedValueException('Expected an array  from config range');
		}

		foreach ($range as $key => $value) {
			$ranges = explode('=', $key);
			$this->ranges[] = new LotteryRange($ranges[0], $ranges[1], (int) $value);
		}
	}

	public function getTables(array $tables = []) : array {
		foreach ($this->ranges as $range) {
			$tables = array_merge($tables, $range->getTable());
		}

		for ($i = 1; $i < mt_rand(5, 11); ++$i) {
			shuffle($tables);
		}

		if (count($tables) < 28) {
			$tables = $this->getTables($tables);
		}

		return $tables;
	}
}