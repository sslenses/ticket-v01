<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

class FakeConnection extends \Illuminate\Database\SQLiteConnection
{
    public static array $mockDatabase = [];
    public static int $lastInsertId = 0;
    public static array $logs = [];

    public function __construct($pdo = null, $database = '', $tablePrefix = '', array $config = [])
    {
        $config['name'] ??= 'sqlite';
        $config['driver'] ??= 'sqlite';
        if (!$pdo) {
            $pdo = \Mockery::mock(\PDO::class)->shouldIgnoreMissing();
            $pdo->shouldReceive('lastInsertId')->andReturnUsing(function() {
                return self::$lastInsertId;
            });
        }
        parent::__construct($pdo, $database, $tablePrefix, $config);
    }

    protected function filterRows($table, $query, $bindings)
    {
        $rows = self::$mockDatabase[$table] ?? [];
        
        $cleanQuery = str_replace(['"', '`'], '', $query);
        preg_match_all('/([\w.]+)\s*=\s*\?/i', $cleanQuery, $matches);
        $columns = $matches[1] ?? [];
        
        if (empty($columns)) {
            return $rows;
        }
        
        $filtered = [];
        foreach ($rows as $row) {
            $match = true;
            foreach ($columns as $index => $col) {
                $parts = explode('.', $col);
                $colName = end($parts);
                
                $bindingValue = $bindings[$index] ?? null;
                if (!isset($row[$colName]) || $row[$colName] != $bindingValue) {
                    $match = false;
                    break;
                }
            }
            if ($match) {
                $filtered[] = $row;
            }
        }
        
        return $filtered;
    }

    public function select($query, $bindings = [], $useReadPdo = true, array $fetchUsing = [])
    {
        self::$logs[] = ['type' => 'select', 'query' => $query, 'bindings' => $bindings];

        preg_match('/from\s+["`]?(\w+)["`]?/i', $query, $tableMatches);
        $table = $tableMatches[1] ?? '';

        // 1. Schema info queries
        if (str_contains($query, 'sqlite_master') || str_contains($query, 'information_schema')) {
            return [];
        }

        // 2. Count queries
        if (str_contains($query, 'count(*)')) {
            $filtered = $this->filterRows($table, $query, $bindings);
            return [(object)['aggregate' => count($filtered)]];
        }

        // 3. Exists queries
        if (str_contains(strtolower($query), 'exists')) {
            $filtered = $this->filterRows($table, $query, $bindings);
            return [(object)['exists' => count($filtered) > 0 ? 1 : 0]];
        }

        // 4. General select queries
        if ($table) {
            $filtered = $this->filterRows($table, $query, $bindings);
            return array_map(fn($row) => (object)$row, $filtered);
        }

        return [];
    }

    public function insert($query, $bindings = [])
    {
        self::$logs[] = ['type' => 'insert', 'query' => $query, 'bindings' => $bindings];

        preg_match('/insert into\s+["`]?(\w+)["`]?/i', $query, $matches);
        $table = $matches[1] ?? null;

        if ($table) {
            preg_match('/\(([^)]+)\)\s+values\s+\(([^)]+)\)/i', $query, $parts);
            if (isset($parts[1])) {
                $columns = array_map(function($c) {
                    return trim($c, ' "`');
                }, explode(',', $parts[1]));
                
                $row = [];
                foreach ($columns as $index => $col) {
                    $row[$col] = $bindings[$index] ?? null;
                }
                
                self::$lastInsertId++;
                $row['id'] = self::$lastInsertId;
                
                if (isset($row['cable_details']) && is_string($row['cable_details'])) {
                    $row['cable_details'] = json_decode($row['cable_details'], true);
                }

                self::$mockDatabase[$table][] = $row;
            }
        }

        return true;
    }

    public function update($query, $bindings = [])
    {
        self::$logs[] = ['type' => 'update', 'query' => $query, 'bindings' => $bindings];

        preg_match('/update\s+["`]?(\w+)["`]?/i', $query, $matches);
        $table = $matches[1] ?? null;

        if ($table) {
            preg_match('/set\s+(.+?)\s+where/i', $query, $sets);
            if (isset($sets[1]) && !empty($bindings)) {
                $setParts = explode(',', $sets[1]);
                $setCols = [];
                foreach ($setParts as $part) {
                    $col = trim(explode('=', $part)[0]);
                    $setCols[] = trim($col, ' "`');
                }

                $id = end($bindings);
                
                if (isset(self::$mockDatabase[$table])) {
                    foreach (self::$mockDatabase[$table] as &$row) {
                        if ($row['id'] == $id) {
                            foreach ($setCols as $index => $col) {
                                $row[$col] = $bindings[$index] ?? null;
                            }
                            break;
                        }
                    }
                }
            }
        }

        return 1;
    }

    public function delete($query, $bindings = [])
    {
        self::$logs[] = ['type' => 'delete', 'query' => $query, 'bindings' => $bindings];
        return 1;
    }

    public function statement($query, $bindings = [])
    {
        self::$logs[] = ['type' => 'statement', 'query' => $query, 'bindings' => $bindings];
        return true;
    }

    public function affectingStatement($query, $bindings = [])
    {
        self::$logs[] = ['type' => 'affectingStatement', 'query' => $query, 'bindings' => $bindings];
        return 1;
    }

    public function beginTransaction() {}
    public function commit() {}
    public function rollBack($toLevel = null) {}
    public function getServerVersion(): string
    {
        return '3.37.0';
    }
}

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        FakeConnection::$mockDatabase = [];
        FakeConnection::$lastInsertId = 0;
        FakeConnection::$logs = [];

        // Boot app and facades
        $this->refreshApplication();

        // Register custom mock SQLite connection
        $this->app->make('db')->extend('sqlite', function ($config, $name) {
            $config['name'] = $name;
            return new FakeConnection(null, $config['database'] ?? '', $config['prefix'] ?? '', $config);
        });

        \Illuminate\Database\Eloquent\Model::setConnectionResolver($this->app->make('db'));

        try {
            parent::setUp();
        } catch (\Throwable $e) {
            $msg = "=== SETUP EXCEPTION ===\n"
                 . "setUp failed: " . $e->getMessage() . "\n"
                 . "File: " . $e->getFile() . " on line " . $e->getLine() . "\n"
                 . "Stack trace:\n";
            $trace = $e->getTrace();
            foreach ($trace as $i => $frame) {
                $msg .= "#$i: " . ($frame['class'] ?? '') . ($frame['type'] ?? '') . $frame['function'] . "\n";
                if (isset($frame['args'])) {
                    $args = array_map(function($a) {
                        return is_object($a) ? get_class($a) : (is_array($a) ? 'array(' . count($a) . ')' : $a);
                    }, $frame['args']);
                    $msg .= "  Args: " . json_encode($args) . "\n";
                }
            }
            file_put_contents('/home/sidiq/Projek/ticket-v01/error.log', $msg, FILE_APPEND);
            throw $e;
        }
    }
}
