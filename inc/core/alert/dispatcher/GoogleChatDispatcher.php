<?php

namespace waboot\inc\core\alert\dispatcher;

use Waboot\inc\core\alert\AbstractAlertDispatcher;
use function Waboot\inc\core\helpers\logError;
use function Waboot\inc\core\helpers\logException;

class GoogleChatDispatcher extends AbstractAlertDispatcher
{
    private string $dispatchToUrl;

    /**
     * @param string $name
     * @param string|null $tz
     * @param string $dispatchToUrl
     */
    public function __construct(string $name, string $dispatchToUrl, string $tz = null)
    {
        parent::__construct($name,$tz);
        $this->name = $name;
        $this->dispatchToUrl = $dispatchToUrl;
    }


    function dispatch(): void
    {
        try{
            foreach ($this->alerts as $alert){
                $r = wp_remote_post($this->dispatchToUrl, [
                    "timeout" => 10,
                    "headers" => [
                        'Content-Type' => 'application/json; charset=utf-8',
                    ],
                    'body' => json_encode([
                        'text' => $alert->getMessage()
                    ])
                ]);
                if(is_wp_error($r)){
                    logError($r->get_error_message(),'GoogleChatDispatcher');
                }
                /**
                 * @var \WP_HTTP_Response $response
                 */
                $response = $r['http_response'];
                if($response->get_status() !== 200){
                    $b = json_decode($response->get_data(), true);
                    if(isset($b['error'])){
                        logError($b['error']['message'],'GoogleChatDispatcher');
                    }
                }
            }
        }catch (\Exception|\Throwable $e) {
            logException($e,'GoogleChatDispatcher');
        }
    }
}