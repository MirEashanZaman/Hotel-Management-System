<?php
abstract class BaseModel {
    protected $db;

    public function __construct() {
        global $conn;
        $this->db = $conn;
    }
}
