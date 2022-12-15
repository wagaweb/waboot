<?php

namespace Waboot\inc\core;

class WPRemoteResponse
{
    private array $rawResponse;
    private array $body;

    /**
     * @param array $response
     * @throws WPRemoteResponseException
     */
    public function __construct(array $response)
    {
        if(
            array_key_exists('headers',$response) &&
            array_key_exists('body',$response) &&
            array_key_exists('response',$response) &&
            array_key_exists('cookies',$response) &&
            array_key_exists('filename',$response)
        ){
            $this->rawResponse = $response;
        }else{
            throw new WPRemoteResponseException('Invalid response provided');
        }
    }

    /**
     * @return array
     * @throws WPRemoteResponseException
     */
    public function getBody(): array
    {
        if(!isset($this->body)){
            $body = json_decode($this->rawResponse['body'],true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new WPRemoteResponseException('json_decode error: ' . json_last_error_msg());
            }
            $this->body = $body;
        }
        return $this->body;
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        $response = $this->getResponse();
        if(!isset($response['code']) || !isset($response['message'])){
            return false;
        }
        return $this->getResponse()['code'] === 200 && $this->getResponse()['message'] === 'OK';
    }

    /**
     * @return bool
     * @throws WPRemoteResponseException
     */
    public function isDataSuccessfull(): bool
    {
        $body = $this->getBody();
        return isset($body['success']) && $body['success'] === true;
    }

    /**
     * @return mixed
     * @throws WPRemoteResponseException
     */
    public function getData()
    {
        $body = $this->getBody();
        if(!isset($body['data'])){
            return '';
        }
        /*
         * If endpoint used wp_send_json_error and the provided $data is a WP_Error, 'data' is an array of arrays like [code => ..., message => ...]
         */
        return $body['data'];
    }

    /**
     * @return array
     */
    public function getResponse(): array
    {
        return $this->rawResponse['response'];
    }

    /**
     * @return string
     */
    public function getResponseMessage(): string
    {
        $response = $this->getResponse();
        if(!isset($response['message'])){
            return '';
        }
        return $response['message'];
    }

    /**
     * @return string
     */
    public function getResponseCode(): string
    {
        $response = $this->getResponse();
        if(!isset($response['code'])){
            return '';
        }
        return $response['code'];
    }
}