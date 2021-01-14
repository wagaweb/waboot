<?php

namespace Waboot\addons\packages\catalog_custom_tables\cli;

use Waboot\addons\packages\catalog_custom_tables\inc\CapsuleWP;
use Waboot\inc\core\cli\AbstractCommand;

class SetupDB extends AbstractCommand
{
    /**
     * @var string
     */
    protected $logDirName = 'db-setup';
    /**
     * @var string
     */
    protected $logFileName = 'db-setup';
    /**
     * @var bool
     */
    protected $verbose = true;

    /**
     * Esegue il setup del DB
     *
     * ## OPTIONS
     *
     * [--reset]
     * : /!\ Resetta il database
     *
     * ## EXAMPLES
     *
     *      wp esp:setup-db
     *
     *      wp esp:setup-db --reset
     */
    public function __invoke($args, $assoc_args)
    {
        $mustReset = isset($assoc_args['reset']);
        if($mustReset){
            $this->reset();
        }
        $this->setup();
        $this->success('Operazione completata');
    }

    public function reset(){
        try{
            $db = new CapsuleWP();
            $db->getBuilder()->dropIfExists(WB_CUSTOM_PRODUCTS_TABLE);
            $db->getBuilder()->dropIfExists(WB_CUSTOM_CATEGORIES_TABLE);
            $db->getBuilder()->dropIfExists(WB_CUSTOM_PRODUCTS_CATEGORIES_TABLE);
            $this->log('Database resettato');
            return 0;
        }catch (\PDOException $e){
            $this->error($e->getMessage());
            return -1;
        }catch (\RuntimeException $e){
            $this->error($e->getMessage());
            return -1;
        }
    }

    public function setup()
    {
        try{
            $db = new CapsuleWP();
            if(!$db->tableExists(WB_CUSTOM_CATEGORIES_TABLE)){
                $db->getBuilder()->create(WB_CUSTOM_CATEGORIES_TABLE, function (\Illuminate\Database\Schema\Blueprint $table){
                    $table->id();
                    $table->integer('wc_id')->unique();
                    $table->string('name');
                    $table->string('slug')->unique();
                    $table->unsignedBigInteger('parent_id')->nullable();
                    //$table->primary('id');
                });
                $this->log('Tabella: '.WB_CUSTOM_CATEGORIES_TABLE.' creata');
            }
            if(!$db->tableExists(WB_CUSTOM_PRODUCTS_TABLE)){
                $db->getBuilder()->create(WB_CUSTOM_PRODUCTS_TABLE, function (\Illuminate\Database\Schema\Blueprint $table){
                    $table->id();
                    $table->integer('wc_id')->unique();
                    $table->string('sku')->unique();
                    $table->foreignId('parent_id')->nullable();
                    $table->string('parent_sku')->nullable();
                    $table->string('title');
                    $table->foreignId('main_category_id')->nullable();
                    $table->float('price')->nullable();
                    $table->integer('stock')->nullable();
                    //$table->primary('id');
                });
                $this->log('Tabella: '.WB_CUSTOM_PRODUCTS_TABLE.' creata');
            }
            if(!$db->tableExists(WB_CUSTOM_PRODUCTS_CATEGORIES_TABLE)){
                $db->getBuilder()->create(WB_CUSTOM_PRODUCTS_CATEGORIES_TABLE, function (\Illuminate\Database\Schema\Blueprint $table){
                    $table->foreignId('product_id');
                    $table->foreignId('category_id');
                    $table->primary(['product_id','category_id'],'wc_wb_products_categories_product_id_category_id_primary');
                });
                $this->log('Tabella: '.WB_CUSTOM_PRODUCTS_CATEGORIES_TABLE.' creata');
            }
            $this->log('Tutte le tabelle sono state create con successo');
            return 0;
        }catch (\PDOException $e){
            $this->error($e->getMessage());
            return -1;
        }catch (\RuntimeException $e){
            $this->error($e->getMessage());
            return -1;
        }
    }
}