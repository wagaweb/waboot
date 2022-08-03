<?php

namespace Waboot\addons\packages\shop_rules;

class ShopRuleRepository
{
    /**
     * @param string $defaultTimeZoneName
     * @return array
     */
    public function getAllShopRules(string $defaultTimeZoneName = 'Europe/Rome'): array
    {
        $option = get_option('wawoo_shop_rules');
        if(!\is_string($option) || $option === ''){
            return [];
        }
        try{
            $decodedOption = jsonDecode($option);
            if(count($decodedOption) === 0){
                return [];
            }
            $rules = [];
            $lastInsertedRuleId = getLastInsertedRuleId();
            foreach ($decodedOption as $ruleArray){
                try{
                    $rule = ShopRule::fromArray($ruleArray,$defaultTimeZoneName);
                    if(!$rule->hasId()){
                        $lastInsertedRuleId++;
                        $rule->setId($lastInsertedRuleId);
                    }
                    $rules[] = $rule;
                }catch (\Exception $e){
                    continue;
                }
            }
            return $rules;
        }catch (\JsonException $e){
            return [];
        }
    }

    /**
     * @param ShopRule[] $rules
     * @return bool
     */
    public function saveRules(array $rules): bool
    {
        $rulesToSave = [];
        foreach ($rules as $rule){
            try{
                if(!$rule->hasId()){
                    $rule->setId(generateRuleIdByRule($rule,true));
                }
                $rulesToSave[] = $rule->toArray();
            }catch (\Exception $e){
                continue;
            }
        }
        try{
            $encodedOption = jsonEncode($rulesToSave);
            $this->deleteAllRules(); //Empty saved rules
            return update_option('wawoo_shop_rules',$encodedOption);
        }catch (\JsonException $e){
            return false;
        }
    }

    /**
     * Update the rules to DB starting from an arrays of ShopRule(s).
     * Every ShopRule must have an integer id (to make crud operations works), so this function assign an incremental
     * integer to each rule.
     *
     * This function does not overwrite existing rules.
     *
     * @param ShopRule[] $rules
     * @return bool
     */
    public function appendRules(array $rules): bool
    {
        $currentRules = $this->getAllShopRules();
        if(!empty($currentRules)){
            $currentRulesNames = array_map(static function(ShopRule $rule){
                return $rule->getName();
            },$currentRules);
        }else{
            $currentRulesNames = [];
        }
        $newRulesArray = [];
        foreach ($rules as $rule){
            if(!$rule instanceof ShopRule){
                continue;
            }
            if(!\in_array($rule->getName(),$currentRulesNames,true)){
                /*if(!$rule->hasId()){
                    $rule->setId(generateRuleIdByRule($rule));
                }*/
                $newRulesArray[] = $rule;
            }
        }
        $newRulesArray = array_merge($currentRules,$newRulesArray); //merging existing rules and new rules
        try{
            return $this->saveRules($newRulesArray);
        }catch (\Exception $e){
            return false;
        }
    }

    /**
     * @param array $shopRuleDetails
     * @throws ShopRuleRepositoryException
     * @return ShopRule
     */
    public function createShopRule(array $shopRuleDetails): ShopRule
    {
        $currentRules = $this->getAllShopRules();
        foreach ($currentRules as $rule){
            if(!$rule instanceof ShopRule){
                continue;
            }
            if(isset($shopRuleDetails['title']) && $rule->getName() === $shopRuleDetails['title']){
                throw new ShopRuleRepositoryException('Rule with the same name already exists');
            }
            if(isset($shopRuleDetails['name']) && $rule->getName() === $shopRuleDetails['name']){
                throw new ShopRuleRepositoryException('Rule with the same name already exists');
            }
        }
        if(!ShopRule::isValidType($shopRuleDetails['type'])){
            throw new ShopRuleRepositoryException('Invalid rule type');
        }
        try{
            if(isset($shopRuleDetails['dates_utc'])){
                try{
                    $tzString = $shopRuleDetails['dates_timezone'] ?? 'Europe/Rome';
                    if(isset($shopRuleDetails['dates_utc'][0])){
                        $dateFrom = new \DateTime($shopRuleDetails['dates'][0],new \DateTimeZone('UTC'));
                        $dateFrom->setTimezone(new \DateTimeZone($tzString));
                        $shopRuleDetails['from'] = $dateFrom->format('c');
                    }
                    if(isset($shopRuleDetails['dates_utc'][1])){
                        $dateTo = new \DateTime($shopRuleDetails['dates'][1],new \DateTimeZone('UTC'));
                        $dateTo->setTimezone(new \DateTimeZone($tzString));
                        $shopRuleDetails['to'] = $dateTo->format('c');
                    }
                    $shopRuleDetails['timezone'] = $tzString;
                    unset($shopRuleDetails['dates_utc'],$shopRuleDetails['dates'],$shopRuleDetails['dates_timezone']);
                }catch (\Exception $e){}
            }
            $newRule = ShopRule::fromArray($shopRuleDetails);
            $currentRules[] = $newRule;
            $created = $this->saveRules($currentRules);
            if(!$created){
                throw new ShopRuleRepositoryException('Shop Rule not created');
            }
            return $newRule;
        }catch (\Exception $e){
            throw new ShopRuleRepositoryException($e->getMessage());
        }
    }

    /**
     * @param int $shopRuleId
     * @param array $shopRuleDetails
     * @return boolean
     * @throws ShopRuleException
     */
    public function updateShopRule(int $shopRuleId, array $shopRuleDetails): bool
    {
        $newRule = ShopRule::fromArray($shopRuleDetails);
        $newRule->setId($shopRuleId);
        $rules = $this->getAllShopRules();
        foreach ($rules as $i => $savedRule){
            if(!$savedRule instanceof ShopRule){
                continue;
            }

            if($savedRule->getId() === $shopRuleId){
                $rules[$i] = $newRule;
            }
        }

        return $this->saveRules($rules);
    }

    /**
     * @param int $shopRuleId
     * @return bool
     * @throws ShopRuleRepositoryException
     */
    public function deleteRule(int $shopRuleId): bool
    {
        $currentRules = $this->getAllShopRules();
        $newRules = [];
        foreach ($currentRules as $rule){
            if(!$rule instanceof ShopRule){
                continue;
            }
            if($rule->getId() !== $shopRuleId){
                $newRules[] = $rule;
            }
        }
        if(!empty($newRules)){
            $saved = $this->saveRules($newRules);
            if(!$saved){
                throw new ShopRuleRepositoryException('Unable to delete the rule');
            }
            return true;
        }
        throw new ShopRuleRepositoryException('Unable to delete the rule');
    }

    /**
     * @return bool
     */
    public function deleteAllRules(): bool
    {
        return delete_option('wawoo_shop_rules');
    }

    /**
     * @param $ruleId
     * @return ShopRule|null
     */
    public function getShopRuleById($ruleId): ?ShopRule
    {
        $rules = $this->getAllShopRules();
        if(empty($rules)){
            return null;
        }
        foreach ($rules as $rule){
            if(!$rule instanceof ShopRule){
                continue;
            }
            if($rule->getId() === $ruleId){
                try{
                    return $rule;
                }catch (\Exception $e){
                    continue;
                }
            }
        }
        $guessedRuleId = 1;
        foreach ($rules as $rule){
            if(!$rule instanceof ShopRule){
                continue;
            }
            if($guessedRuleId === $ruleId){
                try{
                    $rule->setId($guessedRuleId);
                    return $rule;
                }catch (\Exception $e){
                    $guessedRuleId++;
                    continue;
                }
            }
            $guessedRuleId++;
        }
        return null;
    }
}
