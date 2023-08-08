<?php

namespace Waboot\inc\services\mailchimp;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class MailChimpService
{
    public const MAILCHIMP_API_KEY = '';
    public const MAILCHIMP_API_ROOT = 'https://us18.api.mailchimp.com/3.0';
    public const MAILCHIMP_ESPERIRI_MILANO_LIST_ID = '';

    /**
     *
     * @see: https://developer.mailchimp.com/documentation/mailchimp/guides/manage-subscribers-with-the-mailchimp-api/
     *
     * @param string $email
     * @return string
     */
    public static function generateSubscriberHash(string $email): string
    {
        //info@waga.it = 3c8e6d980ba51b68529756abc195dddf
        return md5(strtolower($email));
    }

    /**
     * @param string $listId
     * @param \WP_User $user
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function addUserToList(string $listId, \WP_User $user): void
    {
        $ep = self::MAILCHIMP_API_ROOT.'/lists/'.$listId.'/members';
        $headers = [
            'Authorization' => 'Basic '.base64_encode('mc4wp:'.self::MAILCHIMP_API_KEY),
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ];
        $fname = $user->first_name;
        $lname = $user->last_name;
        $body = [
            'email_address' => $user->user_email,
            'status' => 'subscribed',
            'merge_fields' => [
                'FNAME' => $fname,
                'LNAME' => $lname
            ]
        ];
        $client = new Client();
        $req = new Request('POST',$ep,$headers,\GuzzleHttp\json_encode($body));
        $res = $client->send($req);
    }

    /**
     * @param string $listId
     * @param string $subscriberHash
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function removeMemberFromList(string $listId, string $subscriberHash): void
    {
        $ep = self::MAILCHIMP_API_ROOT.'/lists/'.$listId.'/members/'.$subscriberHash;
        $headers = [
            'Authorization' => 'Basic '.base64_encode('mc4wp:'.self::MAILCHIMP_API_KEY),
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ];
        $client = new Client();
        $req = new Request('DELETE',$ep,$headers);
        $res = $client->send($req);
    }

    /**
     * @param string $listId
     * @param string $subscriberHash
     * @return array|\WP_Error
     * @throws MailChimpAPIException
     */
    public static function getListMemberData(string $listId, string $subscriberHash)
    {
        $ep = self::MAILCHIMP_API_ROOT.'/lists/'.$listId.'/members/'.$subscriberHash;
        $headers = [
            'Authorization' => 'Basic '.base64_encode('mc4wp:'.self::MAILCHIMP_API_KEY),
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ];
        $data = wp_remote_get($ep,[
            'headers' => $headers
        ]);

        if(\is_wp_error($data)){
            throw new MailChimpAPIException($data->get_error_message());
        }

        if(!\is_array($data)){
            throw new MailChimpAPIException('Invalid response received');
        }

        if(!isset($data['response']) || $data['response']['code'] !== 200){
            throw new MailChimpAPIException($data['response']['message']);
        }

        $body = $data['body'];

        $userData = json_decode($body,true);

        if(!\is_array($userData)){
            throw new MailChimpAPIException('Invalid response body received');
        }

        return $userData;
    }

    /**
     * @param string $listId
     * @return array|\WP_Error
     * @throws MailChimpAPIException
     */
    public static function getListMembers(string $listId)
    {
        $ep = self::MAILCHIMP_API_ROOT.'/lists/'.$listId.'/members';
        $headers = [
            'Authorization' => 'Basic '.base64_encode('mc4wp:'.self::MAILCHIMP_API_KEY),
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ];
        $data = wp_remote_get($ep,[
            'headers' => $headers
        ]);

        if(\is_wp_error($data)){
            throw new MailChimpAPIException($data->get_error_message());
        }

        if(!\is_array($data)){
            throw new MailChimpAPIException('Invalid response received');
        }

        if(!isset($data['response']) || $data['response']['code'] !== 200){
            throw new MailChimpAPIException($data['response']['message']);
        }

        $body = $data['body'];

        $usersData = json_decode($body,true);

        if(!\is_array($usersData)){
            throw new MailChimpAPIException('Invalid response body received');
        }

        return $usersData;
    }

    /**
     * @param string $listId
     * @param string $subscriberHash
     * @return bool
     */
    public static function listHasMember(string $listId, string $subscriberHash): bool
    {
        try{
            $userData = self::getListMemberData($listId,$subscriberHash);
            return \is_array($userData);
        }catch (\Exception $e){
            return false;
        }
    }
}