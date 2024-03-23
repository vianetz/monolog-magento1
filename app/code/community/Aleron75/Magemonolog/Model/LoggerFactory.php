<?php
declare(strict_types=1);

use Monolog\Logger;

final class Aleron75_Magemonolog_Model_LoggerFactory
{
    public function create(string $logFile = null): \Psr\Log\LoggerInterface
    {
        $logger = new Logger(Mage::getStoreConfig('magemonolog/name') ?? 'magento');

        $this->addHandlers($logger, $logFile);
        $this->addProcessors($logger);

        return $logger;
    }

    private function addHandlers(\Psr\Log\LoggerInterface $logger, string $logFile = null): void
    {
        $handlers = Mage::getStoreConfig('magemonolog/handlers');
        if (! is_array($handlers)) {
            return;
        }

        foreach ($handlers as $handlerModel => $handlerValues) {
            if (! Mage::getStoreConfigFlag('magemonolog/handlers/' . $handlerModel . '/active')) {
                continue;
            }

            $args = $handlerValues['params'] ?? [];
            if (! empty($logFile)){
                $logFileParts = explode(DS, $logFile);
                $args['stream'] = array_pop($logFileParts);
            }

            $handlerWrapper = Mage::getModel('magemonolog/handlerWrapper_' . $handlerModel, $args);
            if (isset($handlerValues['formatter']['class'])) {
                $formatter = new $handlerValues['formatter']['class'](...$handlerValues['formatter']['args']);
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