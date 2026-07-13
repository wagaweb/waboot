<?php

namespace Waboot\inc\core;

class EmailDisabler
{
    private bool $enabled = false;

    /**
     * @return static|null
     */
    public static function getInstance(): ?EmailDisabler
    {
        static $instance = null;
        if (null === $instance) {
            $instance = new static();
        }

        return $instance;
    }

    protected function __construct()
    {}

    /**
     * @return void
     */
    public function prevent(): void
    {
        add_filter('wp_mail', [$this,'disableRecipients'], 9999, 1);
        add_filter('pre_wp_mail', [$this,'shortcutWpMail'], 9999, 2);
        add_action('phpmailer_init', [$this,'alterPHPMailerInit'], 9999, 1);
        $this->enabled = true;
    }

    /**
     * @return void
     */
    public function allow(): void
    {
        if(!$this->enabled){
            return;
        }
        remove_filter('wp_mail', [$this,'disableRecipients'], 9999);
        remove_filter('pre_wp_mail', [$this,'shortcutWpMail'], 9999);
        remove_action('phpmailer_init', [$this,'alterPHPMailerInit'], 9999);
    }

    /**
     * @param array $args
     * @see: wp_mail()
     * @return array
     */
    public function disableRecipients(array $args): array
    {
        $args['to'] = 'noone@void.void';
        return $args;
    }

    /**
     * @param \PHPMailer $PHPMailer
     * @return void
     */
    public function alterPHPMailerInit(\PHPMailer\PHPMailer\PHPMailer $PHPMailer): void
    {
        $PHPMailer->clearAllRecipients();
        try{
            $PHPMailer->addAddress('noone@void.void');
        }catch (\Exception | \Throwable $e){}
    }

    /**
     * @param $shortCutValue
     * @param array $atts (email recipients, body and such)
     * @see: wp_mail()
     * @return bool
     */
    public function shortcutWpMail($shortCutValue, array $atts): bool
    {
        return true;
    }

    public function __clone()
    {
    }

    public function __wakeup()
    {
    }
}