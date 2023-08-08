<?php

namespace Waboot\addons\packages\invoicing;

class Validator
{
    /**
     * Validate a fiscal code
     *
     * @credit Umberto Salsi <salsi@icosaedro.it>
     *
     * @param string $fiscal_code
     * @param bool $required
     * @return array with 'is_valid' and 'err_message' keys.
     */
    public static function validateFiscalCode(string $fiscal_code, bool $required = true): array
    {
        $fiscal_code = str_replace(' ', '', $fiscal_code);

        $result = [
            'is_valid' => false,
            'err_message' => ''
        ];

        if( $fiscal_code === '' && $required ) {
            $result['err_message'] = sprintf(
                _x("%s is required","WC Field Validation", 'waboot'),
                "<strong>".__("Fiscal code", 'waboot')."<strong>"
            );
            return $result;
        }
        if( strlen($fiscal_code) != 16 ) {
            $result['err_message'] = sprintf(
                _x("%s. Must have 16 character.","WC Field Validation",'waboot'),
                "<strong>"._x("Incorrect fiscal code length","WC Field Validation",'waboot')."<strong>"
            );
            return $result;
        }
        $fiscal_code = strtoupper($fiscal_code);
        if( preg_match("/^[A-Z0-9]+\$/", $fiscal_code) != 1 ){
            $result['err_message'] = sprintf(
                _x("%s. Only letters and numbers are valid.","WC Field Validation",'waboot'),
                "<strong>"._x("Invalid fiscal code","WC Field Validation",'waboot')."<strong>"
            );
            return $result;
        }
        $s = 0;
        for( $i = 1; $i <= 13; $i += 2 ){
            $c = $fiscal_code[$i];
            if( strcmp($c, "0") >= 0 and strcmp($c, "9") <= 0 )
                $s += ord($c) - ord('0');
            else
                $s += ord($c) - ord('A');
        }
        for( $i = 0; $i <= 14; $i += 2 ){
            $c = $fiscal_code[$i];
            switch( $c ){
                case '0':  $s += 1;  break;
                case '1':  $s += 0;  break;
                case '2':  $s += 5;  break;
                case '3':  $s += 7;  break;
                case '4':  $s += 9;  break;
                case '5':  $s += 13;  break;
                case '6':  $s += 15;  break;
                case '7':  $s += 17;  break;
                case '8':  $s += 19;  break;
                case '9':  $s += 21;  break;
                case 'A':  $s += 1;  break;
                case 'B':  $s += 0;  break;
                case 'C':  $s += 5;  break;
                case 'D':  $s += 7;  break;
                case 'E':  $s += 9;  break;
                case 'F':  $s += 13;  break;
                case 'G':  $s += 15;  break;
                case 'H':  $s += 17;  break;
                case 'I':  $s += 19;  break;
                case 'J':  $s += 21;  break;
                case 'K':  $s += 2;  break;
                case 'L':  $s += 4;  break;
                case 'M':  $s += 18;  break;
                case 'N':  $s += 20;  break;
                case 'O':  $s += 11;  break;
                case 'P':  $s += 3;  break;
                case 'Q':  $s += 6;  break;
                case 'R':  $s += 8;  break;
                case 'S':  $s += 12;  break;
                case 'T':  $s += 14;  break;
                case 'U':  $s += 16;  break;
                case 'V':  $s += 10;  break;
                case 'W':  $s += 22;  break;
                case 'X':  $s += 25;  break;
                case 'Y':  $s += 24;  break;
                case 'Z':  $s += 23;  break;
            }
        }
        if( chr($s%26 + ord('A')) != $fiscal_code[15] ) {
            $result['err_message'] = sprintf(
                _x("%s. Wrong control code detected.","WC Field Validation",'waboot'),
                "<strong>"._x("Invalid fiscal code","WC Field Validation",'waboot')."<strong>"
            );
            return $result;
        }
        if (empty($result['err_message'])) {
            $result['is_valid'] = true;
            return $result;
        }else{
            $result['err_message'] = sprintf(
                _x("%s. Unexpected error occurred. Please contact the administration.","WC Field Validation",'waboot'),
                "<strong>"._x("Invalid fiscal code","WC Field Validation",'waboot')."<strong>"
            );
            return $result;
        }
    }

    /**
     * Validate an EU VAT number.
     *
     * @param $vat
     * @param bool $vies_vat
     *
     * @return bool
     */
    public static function validateEuVat($vat, bool $vies_vat): bool
    {
        if($vies_vat){
            return self::validateEuViesVat($vat);
        }

        return self::validateEuSimpleVat($vat);
    }

    /**
     * A simple VAT Validation
     *
     * @param $vat
     *
     * @return bool
     */
    public static function validateEuSimpleVat($vat): bool
    {
        if($vat === "" || !is_string($vat)){
            return false;
        }
        $regex = "|([a-zA-Z]{2,})?[0-9]{11}|";
        if(!preg_match($regex,$vat)){
            return false;
        }
        return true;
    }

    /**
     * Validate an EU VIES VAT number. Uses public EU API.
     *
     * @param $vat
     *
     * @return bool
     */
    public static function validateEuViesVat($vat): bool
    {
        $countries = new \WC_Countries();
        $cc = substr($vat, 0, 2);
        $vn = substr($vat, 2);

        $eu_countries = $countries->get_european_union_countries();

        if (in_array($cc, $eu_countries)) {
            $params = [
                'countryCode' => $cc,
                'vatNumber' => $vn
            ];

            $client = new \SoapClient('http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl');
            $response = $client->__soapCall("checkVat", array($params) );

            if(isset($response->valid) && $response->valid){
                return true;
            }
        }

        return false;
    }
}