<?php

namespace waboot\inc\feeds;

use Waboot\inc\core\utils\Utilities;

class RegisteredFeed
{
    public string $path;
    public ?string $name = null;
    public ?string $commandPrefix = null;

    /**
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * @param array $props
     * @return void
     */
    public function populateProperties(array $props): void
    {
        if(isset($props['name'])){
            $this->name = $props['name'];
        }
        if(isset($props['command_prefix'])){
            $this->commandPrefix = $props['command_prefix'];
        }
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        if(!isset($this->name)){
            return null;
        }
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getCommandPrefix(): ?string
    {
        if(!isset($this->commandPrefix)){
            return null;
        }
        return $this->commandPrefix;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return Utilities::pathToUrl($this->getPath());
    }

    /**
     * @return array
     */
    public function getCLICommandStats(): array
    {
        if(!$this->commandPrefix){
            return [];
        }
        $inProgress = get_option($this->commandPrefix.'_process_in_progress');
        $lastStart = get_option($this->commandPrefix.'_last_started_at');
        $lastEnded = get_option($this->commandPrefix.'_last_ended_at');
        $stats = [];
        $stats['in_progress'] = $inProgress === 'yes';
        if(\is_string($lastStart) && !empty($lastStart)){
            $sD = date_create_from_format('Y-m-d_H-i', $lastStart);
            if($sD instanceof \DateTime){
                $stats['last_started_at'] = $sD->format('Y-m-d H:i');
            }
        }
        if(\is_string($lastEnded) && !empty($lastEnded)){
            $sD = date_create_from_format('Y-m-d_H-i', $lastEnded);
            if($sD instanceof \DateTime){
                $stats['last_ended_at'] = $sD->format('Y-m-d H:i');
            }
        }
        return $stats;
    }
}