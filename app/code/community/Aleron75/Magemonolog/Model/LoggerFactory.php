<?php
declare(strict_types=1);

use Monolog\Logger;

final class Aleron75_Magemonolog_Model_LoggerFactory
{
    private const DEFAULT_CHANNEL_NAME = 'magento';

    public function createFromConfig(?string $channelName = null): \Psr\Log\LoggerInterface
    {
        $logger = new Logger($channelName ?? self::DEFAULT_CHANNEL_NAME);

        $this->addHandlers($logger);
        $this->addProcessors($logger);

        return $logger;
    }

    private function addHandlers(\Psr\Log\LoggerInterface $logger): void
    {
        $handlers = Mage::getStoreConfig('magemonolog/handlers');
        if (! is_array($handlers)) {
            return;
        }

        foreach ($handlers as $config) {
            if (! isset($config['active']) || ! $config['active']) {
                continue;
            }

            $args = $config['params'] ?? [];
            // we can create a separate log file per channel
            if (isset($args['stream'])) {
                $args['stream'] = str_replace('%channel%', $logger->getName(), $args['stream']);
            }

            $handlerWrapper = Mage::getModel($config['class'], $args);
            assert($handlerWrapper instanceof Aleron75_Magemonolog_Model_HandlerWrapper_HandlerInterface);
            if (isset($config['formatter']['class'])) {
                $formatter = new $config['formatter']['class'](...$config['formatter']['args']);
                $handlerWrapper->setFormatter($formatter);
            }

            $logger->pushHandler($handlerWrapper->getHandler());
        }
    }

    private function addProcessors(\Psr\Log\LoggerInterface $logger): void
    {
        $processors = Mage::getStoreConfig('magemonolog/processors');
        if (! is_array($processors)) {
            return;
        }

        foreach ($processors as $config) {
            if (! isset($config['active']) || ! $config['active'] || ! isset($config['class'])) {
                continue;
            }

            $args = $config['params'] ?? [];

            $processor = new $config['class'](...$args);
            $logger->pushProcessor($processor);
        }
    }
}