<?php

namespace Waboot\inc\core\woocommerce\addresses;

use Illuminate\Database\Schema\Blueprint;
use Waboot\inc\core\DBException;
use Waboot\inc\core\facades\Query;
use Waboot\inc\core\woocommerce\Customer;
use function Waboot\inc\core\generateShippingAddressName;
use function Waboot\inc\core\Waboot;

class ShippingAddressRepository
{
    public const TABLE_NAME = 'wawoo_shipping_addresses';

    /**
     * @throws DBException
     */
    public function __construct()
    {
        $this->createTable();
    }

    /**
     * @throws DBException
     */
    public function createTable(bool $recreate = false): void
    {
        if(Waboot()->DB()->tableExists(self::TABLE_NAME)){
            if(!$recreate){
                return;
            }
            Waboot()->DB()->getSchemaBuilder()->dropIfExists(self::TABLE_NAME);
        }
        Waboot()->DB()->getSchemaBuilder()->create(self::TABLE_NAME, function (Blueprint $table){
            $table->id();
            $table->integer('user_id');
            $table->string('name');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('postcode');
            $table->string('country');
            $table->string('company')->nullable();
            $table->string('address_1');
            $table->string('address_2')->nullable();
            $table->string('phone')->nullable();
            do_action('wawoo/multiple_addresses/shipping_address_repository/create_table', $table);
        });
    }

    /**
     * @param Customer $customer
     * @return ShippingAddress|null
     */
    public function getCurrentByCustomer(Customer $customer): ?ShippingAddress
    {
        $sa = false;
        try{
            $currentShippingAddressId = get_user_meta($customer->getId(),AddressUserMetaKeys::currentShippingAddress->value,true);
            if(\is_string($currentShippingAddressId) && !empty($currentShippingAddressId)){
                $sa = $this->findById((int) $currentShippingAddressId);
            }
        }catch (DBException $e){}
        if(!$sa){
            $addressData = [
                'shipping_id' => get_user_meta($customer->getWcCustomer()->get_id(),'shipping_id',true),
                'shipping_first_name' => get_user_meta($customer->getWcCustomer()->get_id(),'shipping_first_name',true),
                'shipping_last_name' => get_user_meta($customer->getWcCustomer()->get_id(),'shipping_last_name',true),
                'shipping_city' => get_user_meta($customer->getWcCustomer()->get_id(),'shipping_city',true),
                'shipping_state' => get_user_meta($customer->getWcCustomer()->get_id(),'shipping_state',true),
                'shipping_postcode' => get_user_meta($customer->getWcCustomer()->get_id(),'shipping_postcode',true),
                'shipping_country' =>  get_user_meta($customer->getWcCustomer()->get_id(),'shipping_country',true),
                'shipping_company' => get_user_meta($customer->getWcCustomer()->get_id(),'shipping_company',true),
                'shipping_address_1' => get_user_meta($customer->getWcCustomer()->get_id(),'shipping_address_1',true),
                'shipping_address_2' => get_user_meta($customer->getWcCustomer()->get_id(),'shipping_address_2',true),
                'shipping_phone' => get_user_meta($customer->getWcCustomer()->get_id(),'shipping_phone',true)
            ];
            $name = $addressData['shipping_id'];
            $firstName = $addressData['shipping_first_name'];
            $lastName = $addressData['shipping_last_name'];
            $city = $addressData['shipping_city'];
            $state = $addressData['shipping_state'];
            $postcode = $addressData['shipping_postcode'];
            $country = $addressData['shipping_country'];
            $company = $addressData['shipping_company'];
            $address1 = $addressData['shipping_address_1'];
            $address2 = $addressData['shipping_address_2'];
            $phone = $addressData['shipping_phone'];
            $sa = new ShippingAddress();
            $sa->setUserId($customer->getWcCustomer()->get_id());
            if(\is_string($name) && !empty($name)){
                $sa->setName($name);
            }
            $sa->setFirstName($firstName);
            $sa->setLastName($lastName);
            $sa->setCity($city);
            $sa->setState($state);
            $sa->setPostCode($postcode);
            $sa->setCountry($country);
            $sa->setCompany($company);
            $sa->setAddress1($address1);
            $sa->setAddress2($address2);
            $sa->setPhone($phone);
        }
        do_action('wawoo/multiple_addresses/shipping_address_repository/get_current_by_customer', $sa, $customer);
        if(!$sa instanceof ShippingAddress){
            return null;
        }
        return $sa;
    }

    public function setCurrentToCustomer(ShippingAddress $address, Customer $customer): void
    {
        update_user_meta($customer->getWcCustomer()->get_id(), 'shipping_id', $address->getName());
        update_user_meta($customer->getWcCustomer()->get_id(), 'shipping_first_name', $address->getFirstName());
        update_user_meta($customer->getWcCustomer()->get_id(), 'shipping_last_name', $address->getLastName());
        update_user_meta($customer->getWcCustomer()->get_id(), 'shipping_city', $address->getCity());
        update_user_meta($customer->getWcCustomer()->get_id(), 'shipping_state', $address->getState());
        update_user_meta($customer->getWcCustomer()->get_id(), 'shipping_postcode', $address->getPostCode());
        update_user_meta($customer->getWcCustomer()->get_id(), 'shipping_country', $address->getCountry());
        update_user_meta($customer->getWcCustomer()->get_id(), 'shipping_address_1', $address->getAddress1());
        if($address->getAddress2()){
            update_user_meta($customer->getWcCustomer()->get_id(), 'shipping_address_2', $address->getAddress2());
        }
        if($address->getCompany()){
            update_user_meta($customer->getWcCustomer()->get_id(), 'shipping_company', $address->getCompany());
        }
        if($address->getPhone()){
            update_user_meta($customer->getWcCustomer()->get_id(), 'shipping_phone', $address->getPhone());
        }
        do_action('wawoo/multiple_addresses/shipping_address_repository/set_current_by_customer', $address, $customer);
    }

    /**
     * @throws DBException
     */
    public function findById(int $id): ?ShippingAddress
    {
        $record = Query::on(self::TABLE_NAME)->select('*')->where(['id' => $id])->get()->first();
        if(!$record){
            return null;
        }
        $factory = new ShippingAddressFactory();
        return $factory->createFromDBRecord($record);
    }

    /**
     * @throws DBException
     */
    public function findByCustomer(Customer $customer): array
    {
        $records = Query::on(self::TABLE_NAME)->select('*')->where(['user_id' => $customer->getWcCustomer()->get_id()])->get()->toArray();
        if(empty($records)){
            return [];
        }
        $factory = new ShippingAddressFactory();
        return array_map(static function($record) use($factory){
            return $factory->createFromDBRecord($record);
        }, $records);
    }

    /**
     * @param string $name
     * @param Customer $customer
     * @return ShippingAddress|null
     * @throws DBException
     */
    public function findByNameAndCustomer(string $name, Customer $customer): ?ShippingAddress
    {
        $record = Query::on(self::TABLE_NAME)->select('*')->where([
            ['name', $name],
            ['user_id', $customer->getWcCustomer()->get_id()],
        ])->get()->first();
        if(!$record instanceof \stdClass){
            return null;
        }
        return (new ShippingAddressFactory())->createFromDBRecord($record);
    }

    /**
     * @param ShippingAddress $address
     * @return void
     * @throws DBException
     */
    public function save(ShippingAddress $address): void
    {
        $record = [
            'user_id' => $address->getUserId(),
            'name' => $address->getName(),
            'first_name' => $address->getFirstName(),
            'last_name' => $address->getLastName(),
            'city' => $address->getCity(),
            'state' => $address->getState(),
            'postcode' => $address->getPostcode(),
            'country' => $address->getCountry(),
            'address_1' => $address->getAddress1(),
        ];
        if($address->getAddress2()){
            $record['address_2'] = $address->getAddress2();
        }
        if($address->getCompany()){
            $record['company'] = $address->getCompany();
        }
        if($address->getPhone()){
            $record['phone'] = $address->getPhone();
        }
        $record = apply_filters('wawoo/multiple_addresses/shipping_address_repository/save_record', $record, $address);
        if(is_int($address->getId())){
            Query::on(self::TABLE_NAME)
                ->where('id', $address->getId())
                ->update($record);
        }else{
            $existingId = Query::on(self::TABLE_NAME)->select('id')->where([
                ['name', $address->getName()],
                ['user_id', $address->getUserId()],
            ])->get()->pluck('id')->first();
            if($existingId){
                Query::on(self::TABLE_NAME)
                    ->where('id', $existingId)
                    ->update($record);
                $address->setId($existingId);
            }else{
                $newId = Query::on(self::TABLE_NAME)->insertGetId($record);
                $address->setId($newId);
            }
        }
    }

    /**
     * @param int $customerId
     * @return void
     */
    public function deleteByCustomerId(int $customerId): void
    {
        try{
            Query::on(self::TABLE_NAME)->where('user_id', $customerId)->delete();
        }catch (\Exception | \Throwable $e){}
    }
}