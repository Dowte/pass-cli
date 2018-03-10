<?php

namespace Dowte\Password\pass\db;


interface BaseActiveRecordInterface
{
    /**
     * @return integer;
     */
    public function save();

    /**
     * @param array $conditions
     * @return bool
     */
    public function delete(array $conditions);
}