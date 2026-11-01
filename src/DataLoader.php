<?php

class DataLoader {
    private string $filePath;

    public function __construct(string $filePath) {
        $this->filePath = $filePath;
    }

    public function loadRecords(): array {
        if(!file_exists($this->filePath) || !is_readable($this->filePath)) {
            throw new Exception("File not found");
        }
        $fileInfo = file_get_contents($this->filePath);
        if(!$fileInfo) {
            throw new Exception("Failed to read file");
        }
        $data = json_decode($fileInfo, true);
        if(json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON format");
        }
        $records = $data['records'] ?? null;
        if(!is_array($records)) {
            throw new Exception("Invalid records format");
        }
        if(count($records) < 1000){
            throw new Exception("Records count is less than 1000");
        }
        return $records;
    }
}

?>