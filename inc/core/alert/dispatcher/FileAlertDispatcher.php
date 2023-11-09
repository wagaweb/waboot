<?php

namespace Waboot\inc\core\alert\dispatcher;

use waboot\inc\core\alert\AbstractAlertDispatcher;
use Waboot\inc\core\alert\AlertDispatcherException;

class FileAlertDispatcher extends AbstractAlertDispatcher
{
    /**
     * @var string
     */
    protected $dispatchTo;

    /**
     * @param string $name
     * @param string $dispatchTo
     * @param string|null $tz
     */
    public function __construct(string $name, string $dispatchTo, string $tz = null)
    {
        parent::__construct($name,$tz);
        $this->name = $name;
        $this->dispatchTo = $dispatchTo;
    }

    function dispatch(): void
    {
        $fileContent = '';
        foreach ($this->alerts as $alert){
            $fileContent .= '### '.$alert->getTimeStamp().PHP_EOL;
            $fileContent .= $alert->getTitle().PHP_EOL;
            $fileContent .= $alert->getMessage().PHP_EOL;
            $fileContent .= '###'.PHP_EOL.PHP_EOL;
        }
        if(!\is_dir($this->dispatchTo) && !\wp_mkdir_p($this->dispatchTo)){
            throw new AlertDispatcherException('Unable to write the alert log to: '.$this->dispatchTo.': directory not found',0,null,$this->alerts);
        }
        try {
            $now = new \DateTime('now', $this->timeZone);
        } catch (\Exception $e) {
            throw new AlertDispatcherException($e->getMessage());
        }
        $fileName = $now->format('Y-m-d_h-i_').sanitize_title($this->name).'.alerts';
        $filePath = $this->dispatchTo.'/'.$fileName;
        $r = file_put_contents($filePath,$fileContent,FILE_APPEND);
        if($r ===  false){
            throw new AlertDispatcherException('Unable to write the alert log to: '.$filePath,0,null,$this->alerts);
        }
    }
}