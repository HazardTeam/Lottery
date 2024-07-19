<?php

namespace hazardteam\lottery;

class LotteryRange {
    public function __construct(private float|int $startRange, private float|int $endRange, private int $chance) {}

    public function getStartRange() : float|int {
        return $this->startRange;
    }

    public function getEndRange() : float|int {
        return $this->endRange;
    }

    public function getChance() : int {
        return $this->chance;
    }

    public function getTable() {
        $table = [];
        for($i = 1; $i <= $this->chance; $i++){
            $table[] = mt_rand($this->startRange * 100, $this->endRange * 100) / 100;
        }
        return $table;
    }
}