<?php
declare(strict_types=1);

use Monolog\Logger;
use Monolog\Processor\ProcessorInterface;
use Psr\Log\LoggerInterface;

final class Aleron75_Magemonolog_Model_LoggerFactory
{
    private const DEFAULT_CHANNEL_NAME = 'magento';
    private LoggerInterface $logger;

    public function createFromConfig(?string $channelName = null): LoggerInterface
    {
        $this->logger = new Logger($channelName ?? self::DEFAULT_CHANNEL_NAME);

        $this->addHandlers();
        $this->addProcessors();

        return $this->logger;
    }

    private function addHandlers(): void
    {
        $handlers = Mage::getStoreConfig('magemonolog/handlers');
        if (! is_array($handlers)) {
            return;
        }

        foreach ($handlers as $config) {
            if (! isset($config['active']) || ! $config['active'] || ! isset($config['class'])) {
                continue;
            }

            $args = $this->prepareParams($config['params'] ?? []);

            $handlerWrapper = Mage::getModel($config['class'], $args);
            assert($handlerWrapper instanceof Aleron75_Magemonolog_Model_HandlerWrapper_HandlerInterface);
            if (isset($config['formatter']['class'])) {
                $formatter = new $config['formatter']['class'](...$config['formatter']['args']);
                $handlerWrapper->setFormatter($formatter);
            }

            $this->logger->pushHandler($handlerWrapper->getHandler());
        }
    }

    private function addProcessors(): void
    {
        $processors = Mage::getStoreConfig('magemonolog/processors');
        if (! is_array($processors)) {
            return;
        }

        foreach ($processors as $config) {
            if (! isset($config['active']) || ! $config['active'] || ! isset($config['class'])) {
                continue;
            }

            $args = $this->prepareParams($config['params'] ?? []);

            $processor = new $config['class'](...$args);
            assert($processor instanceof ProcessorInterface);
            $this->logger->pushProcessor($processor);
        }
    }

    private function prepareParams(array $args): array
    {
        // convert boolean values
        $args = array_map(fn($value) => \in_array($value, ['true', 'false']) ? $value == 'true' : $value, $args);

        // we can create a separate log file per channel
        if (isset($args['stream'])) {
            $args['stream'] = str_replace('%channel%', $this->logger->getName(), $args['stream']);
        }

        return $args;
    }
}