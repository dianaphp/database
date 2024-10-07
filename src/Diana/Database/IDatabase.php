<?php

namespace Diana\Database;

interface IDatabase {
    public function query(string $query);
}