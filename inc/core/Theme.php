<?php

namespace Waboot\inc\core;

use Waboot\inc\core\mvc\HTMLView;
use Waboot\inc\core\utils\Dates;

class Theme{
	public const LOG_LEVEL_DEBUG = 0;
	public const LOG_LEVEL_INFO = 1;
	public const LOG_LEVEL_NOTICE = 2;
	public const LOG_LEVEL_WARNING = 3;
	public const LOG_LEVEL_ERROR = 4;
	public const LOG_LEVEL_CRITICAL = 5;
	public const LOG_LEVEL_ALERT = 6;
	public const LOG_LEVEL_EMERGENCY = 7;
    private ?Layout $layoutHandler = null;
    private ?AssetsManager $assetsManager = null;
	private array $registeredFileLoggers;

	/**
	 * @param AssetsManager $assetsManager
	 * @param Layout $layout
	 */
	public function __construct(AssetsManager $assetsManager, Layout $layout)
    {
        $this->assetsManager = $assetsManager;
        $this->layoutHandler = $layout;
    }

	/**
	 * @return AssetsManager
	 */
	public function getAssetsManager(): AssetsManager
    {
        return $this->assetsManager;
    }

	/**
	 * @return Layout
	 */
	public function getLayoutHandler(): Layout
    {
        return $this->layoutHandler;
    }

	/**
	 * @return void
	 */
	public function loadDependecies(): void
    {
        $deps = [
            'inc/core/hooks.php',
            'inc/template-functions.php',
            'inc/template-rendering.php',
            'inc/template-tags.php',
            'inc/hooks/hooks.php',
            'inc/hooks/init.php',
            'inc/hooks/layout.php',
            'inc/hooks/posts-and-pages.php',
            'inc/hooks/widget-areas.php',
            'inc/hooks/assets.php'
        ];
        safeRequireFiles($deps);
    }

    /**
     * @param string $templateFile
     * @param array $vars
     * @param bool $clean
     */
    public function renderView(string $templateFile, array $vars = [], bool $clean = false): void
    {
        try{
            $v = new HTMLView($templateFile);
            if($clean){
                $v->clean()->display($vars);
            }else{
                $v->display($vars);
            }
        }catch (\Exception $e){
            echo $e->getMessage();
        }
    }

	/**
	 * @param string $command
	 * @param string|callable $callable
	 * @param string $prefix
	 * @param array $description
	 * @return void
	 * @throws ThemeException
	 */
	public function registerCommand(string $command, $callable, string $prefix = '', array $description = []): void
	{
		if(!class_exists('\WP_CLI')){
			return;
		}
		try{
			if($prefix !== ''){
				$command = $prefix.':'.$command;
			}
			if(empty($description)){
				if(class_exists($callable) && method_exists($callable,'getCommandDescription')){
					$description = $callable::getCommandDescription();
				}
			}
			if(\is_array($description) && !empty($description)){
				\WP_CLI::add_command($command,$callable,$description);
			}else{
				\WP_CLI::add_command($command,$callable);
			}
		}catch (\Exception | \Throwable $e){
			throw new ThemeException($e->getMessage());
		}
	}

	/**
	 * @param string $loggerIdentifier
	 * @param string $logMessage
	 * @param int $logLevel
	 * @param array $context
	 * @param \DateTimeZone|null $dz
	 * @return void
	 */
	public function logToFile(string $loggerIdentifier, string $logMessage, int $logLevel = self::LOG_LEVEL_INFO, array $context = [], \DateTimeZone $dz = null)
	{
		try{
			if($dz === null){
				$dz = Dates::getDefaultDateTimeZone();
			}
			$logger = $this->registeredFileLoggers[$loggerIdentifier] ?? null;
			if($logger === null){
				$logFile = WP_CONTENT_DIR.'/logs/'.$loggerIdentifier.'-'.(new \DateTime('now', $dz))->format('Y-m-d').'.log';
				$logger = LoggerFactory::create('user-update', $logFile);
				$this->registeredFileLoggers[$loggerIdentifier] = $logger;
			}
			switch($logLevel){
				case self::LOG_LEVEL_DEBUG:
					$logger->debug($logMessage,$context);
					break;
				case self::LOG_LEVEL_INFO:
					$logger->info($logMessage,$context);
					break;
				case self::LOG_LEVEL_NOTICE:
					$logger->notice($logMessage,$context);
					break;
				case self::LOG_LEVEL_WARNING:
					$logger->warning($logMessage,$context);
					break;
				case self::LOG_LEVEL_ERROR:
					$logger->error($logMessage,$context);
					break;
				case self::LOG_LEVEL_CRITICAL:
					$logger->critical($logMessage,$context);
					break;
				case self::LOG_LEVEL_ALERT:
					$logger->alert($logMessage,$context);
					break;
				case self::LOG_LEVEL_EMERGENCY:
					$logger->emergency($logMessage,$context);
					break;
			}
		}catch (\Exception | \Throwable $e){}
	}
}