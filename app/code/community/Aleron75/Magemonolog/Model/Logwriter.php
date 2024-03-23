<?php
declare(strict_types=1);

use Monolog\Logger;

final class Aleron75_Magemonolog_Model_Logwriter extends Zend_Log_Writer_Abstract
{
    private Monolog\Logger $logger;

    /** Array used to map Zend's log levels into Monolog's */
    private array $levelMap = [
        Zend_Log::EMERG => \Monolog\Level::Emergency,
        Zend_Log::ALERT => \Monolog\Level::Alert,
        Zend_Log::CRIT => \Monolog\Level::Critical,
        Zend_Log::ERR => \Monolog\Level::Error,
        Zend_Log::WARN => \Monolog\Level::Warning,
        Zend_Log::NOTICE => \Monolog\Level::Notice,
        Zend_Log::INFO => \Monolog\Level::Info,
        Zend_Log::DEBUG => \Monolog\Level::Debug,
    ];

    /** @throws \ReflectionException */
    public function __construct(string $logFile)
    {
        $channelName = Mage::helper('magemonolog')->getChannelNameByFile($logFile);
        $this->logger = Mage::getSingleton('magemonolog/loggerFactory')->createFromConfig($channelName);
    }

    /** @param array $event event data */
    protected function _write($event): void
    {
        $level = $this->levelMap[$event['priority']];
        $this->logger->addRecord($level, $event['message']);
    }

    /**
     * @param array|Zend_Config $config
     *
     * @throws \Zend_Log_Exception|\ReflectionException
     */
    static public function factory($config): self
    {
        return new self(self::_parseConfig($config));
    }
}
