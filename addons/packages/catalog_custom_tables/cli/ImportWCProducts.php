<?php

namespace Waboot\addons\packages\catalog_custom_tables\cli;

use Waboot\addons\packages\catalog_custom_tables\inc\CapsuleWP;
use Waboot\addons\packages\catalog_custom_tables\inc\CatalogDB;
use Waboot\addons\packages\catalog_custom_tables\inc\DBProduct;
use Waboot\addons\packages\catalog_custom_tables\inc\DBProductException;
use Waboot\inc\core\cli\AbstractCommand;

class ImportWCProducts extends AbstractCommand
{
    /**
     * @var string
     */
    protected $logDirName = 'wc-importer';
    /**
     * @var string
     */
    protected $logFileName = 'wc-importer';
    /**
     * @var CapsuleWP
     */
    protected $dbConnector;
    /**
     * @var CatalogDB;
     */
    protected $dbManager;

    /**
     * Importa i prodotti per WooCommerce dalla tabella in cui sono stati importati i prodotti da CSV
     *
     * ## OPTIONS
     *
     * [--progress]
     * : Mostra la progress bar
     *
     * ## EXAMPLES
     *
     *      wp esp:import-wc-products
     */
    public function __invoke($args, $assoc_args)
    {
        $this->setupDefaultFlags($assoc_args);
        if(!$this->isVerbose()){
            $this->suppressErrors();
        }
        $this->import();
    }

    public function import()
    {
        try{
            $this->dbConnector = new CapsuleWP();
            $this->dbManager = new CatalogDB($this->dbConnector);

            $this->dbConnector->getManager()::table(WB_CUSTOM_CATEGORIES_TABLE)->truncate();
            $this->log('Tabella: '.WB_CUSTOM_CATEGORIES_TABLE.' troncata');
            $this->dbConnector->getManager()::table(WB_CUSTOM_PRODUCTS_CATEGORIES_TABLE)->truncate();
            $this->log('Tabella: '.WB_CUSTOM_PRODUCTS_CATEGORIES_TABLE.' troncata');

            $productIds = get_posts([
                'post_type' => ['product'],
                'posts_per_page' => -1,
                'fields' => 'ids'
            ]);

            $progress = $this->progressBarAvailable() && !$this->isVerbose() ? $this->makeProgressBar('Importing products', count($productIds)) : false;
            foreach ($productIds as $productId){
                try{
                    $dbProduct = new DBProduct($productId, $this->dbManager, $this->dbConnector);
                    $newId = $dbProduct->save();
                    if($dbProduct->isNew()){
                        $this->log('Product '.$dbProduct->getSku().' inserted with ID: #'.$newId);
                    }else{
                        $this->log('Product '.$dbProduct->getSku().' with ID: #'.$newId.' updated');
                    }
                    if($dbProduct->isParent()){
                        $variations = $dbProduct->getWcProduct()->get_available_variations();
                    }
                }catch (DBProductException $e){
                    $this->log('Unable to save product: '.$e->getMessage());
                    continue;
                }catch (\WC_Data_Exception $e){
                    $this->log('Unable to save product: '.$e->getMessage());
                    continue;
                }catch (\Illuminate\Database\QueryException $e){
                    $this->log('Unable to save product: '.$e->getMessage());
                    continue;
                }catch (\RuntimeException $e){
                    $this->log('Unable to save product: '.$e->getMessage());
                    continue;
                }
                if($this->isProgressBar($progress)){
                    $progress->tick();
                }
            }
        }catch (\RuntimeException $e){
            return -1;
        }

        $this->success('Operation completed');
        return 0;
    }
}