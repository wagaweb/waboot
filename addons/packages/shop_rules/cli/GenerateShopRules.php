<?php

namespace Waboot\addons\packages\shop_rules\cli;

use Waboot\addons\packages\shop_rules\ShopRuleRepository;
use Waboot\inc\core\cli\AbstractCommand;
use function Waboot\addons\packages\shop_rules\getRulesFromTheme;

class GenerateShopRules extends AbstractCommand
{
    /**
     * @var string
     */
    protected $logDirName = 'generate-shop-rules';
    /**
     * @var string
     */
    protected $logFileName = 'generate-shop-rules';

    /**
     * Parse rule files inside the active theme and generate shop rules
     *
     * ## EXAMPLES
     *
     *      wp wawoo:generate-shop-rules
     */
    public function __invoke($args, $assoc_args): int
    {
        try{
            $this->log('Searching for rules...');
            $themeRules = getRulesFromTheme();
            if(!\is_array($themeRules) || count($themeRules) === 0){
                $this->log('No rules found');
                return 0;
            }
            $this->log(sprintf('Found %d rules',count($themeRules)));
            $this->log('Updating rules...');
            $updated = (new ShopRuleRepository())->appendRules($themeRules);
            $this->success('Operation completed');
            return 0;
        }catch (\Exception $e){
            $this->error($e->getMessage(),false);
            return 1;
        }
    }
}