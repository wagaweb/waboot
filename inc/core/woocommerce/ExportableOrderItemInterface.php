<?php

namespace Waboot\inc\core\woocommerce;

interface ExportableOrderItemInterface
{
    /**
     * @return bool
     */
    public function canBeAddedAsItem(): bool;

    /**
     * @return array
     */
    public function generateExportData(): array;
}