<?php

declare(strict_types=1);

namespace AgentForge\Dashboard;

final class JsonStore
{
    public function __construct(private readonly string $filePath)
    {
        $dir = dirname($this->filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        if (!file_exists($this->filePath)) {
            file_put_contents($this->filePath, json_encode([], JSON_PRETTY_PRINT));
        }
    }

    public function all(): array
    {
        $content = file_get_contents($this->filePath);
        if ($content === false || $content === '') {
            return [];
        }

        $decoded = json_decode($content, true);
        return is_array($decoded) ? $decoded : [];
    }

    public function writeAll(array $rows): void
    {
        file_put_contents($this->filePath, json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    public function nextId(): int
    {
        $rows = $this->all();
        if ($rows === []) {
            return 1;
        }

        $maxId = 0;
        foreach ($rows as $row) {
            $rowId = (int)($row['id'] ?? 0);
            $maxId = max($maxId, $rowId);
        }

        return $maxId + 1;
    }

    public function append(array $row): array
    {
        $rows = $this->all();
        $rows[] = $row;
        $this->writeAll($rows);
        return $row;
    }

    public function updateWhere(callable $matchFn, callable $transformFn): ?array
    {
        $rows = $this->all();
        $updated = null;

        foreach ($rows as $idx => $row) {
            if ($matchFn($row)) {
                $rows[$idx] = $transformFn($row);
                $updated = $rows[$idx];
                break;
            }
        }

        if ($updated !== null) {
            $this->writeAll($rows);
        }

        return $updated;
    }

    public function firstWhere(callable $matchFn): ?array
    {
        $rows = $this->all();
        foreach ($rows as $row) {
            if ($matchFn($row)) {
                return $row;
            }
        }

        return null;
    }

    public function deleteWhere(callable $matchFn): ?array
    {
        $rows = $this->all();
        $deleted = null;
        $remaining = [];

        foreach ($rows as $row) {
            if ($deleted === null && $matchFn($row)) {
                $deleted = $row;
                continue;
            }

            $remaining[] = $row;
        }

        if ($deleted !== null) {
            $this->writeAll($remaining);
        }

        return $deleted;
    }
}
