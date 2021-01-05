<?php

namespace Waboot\addons\packages\catalog_custom_tables\cli;

use Waboot\addons\packages\catalog_custom_tables\inc\AbstractCommand;
use Waboot\addons\packages\catalog_custom_tables\inc\CapsuleWP;

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
            $records = $this->dbConnector->getManager()::table(TRADE_AREA_PRODUCTS_TABLE)
                ->where('ecommerce', '=', 1)
                ->get();
            if(count($records) <= 0){
                $this->error('Non ci sono prodotti importabili');
                return -1;
            }
            $alreadySavedParents = [];
            $progress = $this->progressBarAvailable() && !$this->isVerbose() ? $this->makeProgressBar('Importazione prodotti per ecommerce', count($records)) : false;
            foreach ($records as $record){
                try{
                    $ecProduct = new ECommerceProduct($record, $this->dbConnector);
                    if($ecProduct->isParent()){
                        $newId = $ecProduct->save();
                        $alreadySavedParents[] = $ecProduct->getSku();
                        if($ecProduct->isNew()){
                            $this->log('Prodotto '.$ecProduct->getSku().' inserito con ID: #'.$newId);
                        }else{
                            $this->log('Prodotto '.$ecProduct->getSku().' con ID: #'.$newId.' aggiornato');
                        }
                    }else{
                        $parentEcProduct = $ecProduct->getParent();
                        if($parentEcProduct instanceof ECommerceProduct && !\in_array($parentEcProduct->getSku(), $alreadySavedParents, true)){
                            $newId = $parentEcProduct->save();
                            $alreadySavedParents[] = $parentEcProduct->getSku();
                            if($parentEcProduct->isNew()){
                                $this->log('Prodotto '.$parentEcProduct->getSku().' inserito con ID: #'.$newId);
                            }else{
                                $this->log('Prodotto '.$parentEcProduct->getSku().' con ID: #'.$newId.' aggiornato');
                            }
                        }
                    }
                }catch (\WC_Data_Exception $e){
                    $this->log('Impossibile salvare prodotto: '.$e->getMessage());
                    continue;
                }
                catch (\Illuminate\Database\QueryException $e){
                    $this->log('Impossibile salvare prodotto: '.$e->getMessage());
                    continue;
                }catch (\RuntimeException $e){
                    $this->log('Impossibile salvare prodotto: '.$e->getMessage());
                    continue;
                }
                if($this->isProgressBar($progress)){
                    $progress->tick();
                }
            }
        }catch (\RuntimeException $e){
            return -1;
        }

        $this->success('Operazione completata');
        return 0;
    }
}