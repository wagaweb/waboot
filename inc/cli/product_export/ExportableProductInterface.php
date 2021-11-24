<?php

namespace Waboot\inc\cli\product_export;

interface ExportableProductInterface
{
    /**
     * @param array $columnData
     * @return array
     */
    public function createRecord(array $columnData): array;
}