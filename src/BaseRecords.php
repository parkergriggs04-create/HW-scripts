<?php
abstract class BaseRecords {
    protected array $records = [];
    protected int $recordSize = 0;

    public function __construct(array $records = []) {
        $this->records = $records;
        $this->recordSize = $this->getRecordSize($this->records);
    }

    public function computePercentage(int $upper, int $lower, $precision = 2) {
        if($lower == 0){
            return 0.0;
        }
        return round(($upper / $lower) * 100, $precision);
    }

    abstract public function analyze(): array;

    public function getRecordSize(array $records = null): int {
        if($records === null){
            return $this->recordSize;
        }
        return count($records);
    }
}
