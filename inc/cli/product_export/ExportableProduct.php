<?php

namespace Waboot\inc\cli\product_export;

use Waboot\inc\core\utils\Utilities;
use function Waboot\inc\getTheTermsListHierarchical;

class ExportableProduct extends AbstractExportableProduct
{
    public function __construct(\WC_Product $product)
    {
        parent::__construct($product);
    }
}