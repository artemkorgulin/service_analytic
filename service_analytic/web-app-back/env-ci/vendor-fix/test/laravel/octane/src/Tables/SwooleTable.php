<?php

namespace Laravel\Octane\Tables;

use Swoole\Table;

class SwooleTable extends Table
{
    use Concerns\EnsuresColumnSizes;

    /**
     * The table columns.
     *
     * @var array
     */
    protected $columns;

    /**
     * Set the data type and size of the columns.
     *
     * @param string $name
     * @param int $type
     * @param int $size
     * @return bool
     */
    public function column(string $name, int $type, int $size = 0): bool
    {
        $this->columns[$name] = [$type, $size];

        parent::column($name, $type, $size);

        return true;
    }

    /**
     * Update a row of the table.
     *
     * @param string $key
     * @param array $values
     * @return bool
     */
    public function set(string $key, array $values): bool
    {
        collect($values)
            ->each($this->ensureColumnsSize());

        parent::set($key, $values);

        return true;
    }
}
