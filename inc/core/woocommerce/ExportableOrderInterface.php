<?php

namespace Waboot\inc\core\woocommerce;

interface ExportableOrderInterface
{
    /**
     * @return void
     */
    public function setAsExported(): void;

    /**
     * @return bool
     */
    public function isAlreadyExported(): bool;

    /**
     * @return array
     */
    public function generateExportData(): array;

    /**
     * @return bool
     */
    public function export(bool $dryRun = false): bool;
}
