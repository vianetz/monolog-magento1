<?php
declare(strict_types=1);

use Monolog\Logger;

final class Aleron75_Magemonolog_Model_LoggerFactory
{
    public static function create(string $logFile = null): \Psr\Log\LoggerInterface
    {
        $logger = new Logger(Mage::getStoreConfig('magemonolog/name') ?? 'magento');

        $handlers = Mage::getStoreConfig('magemonolog/handlers');
        if (! is_array($handlers)) {
            return $logger;
        }

        foreach ($handlers as $handlerModel => $handlerValues) {
            if (! Mage::getStoreConfigFlag('magemonolog/handlers/' . $handlerModel . '/active')) {
                continue;
            }

            $args = [];
            if (array_key_exists('params', $handlerValues)) {
                $args = $handlerValues['params'];
                if (! empty($logFile)){
                    $logFileParts = explode(DS, $logFile);
                    $args['stream'] = array_pop($logFileParts);
                }
            }

            $handlerWrapper = Mage::getModel('magemonolog/handlerWrapper_' . $handlerModel, $args);
            if (array_key_exists('formatter', $handlerValues) && array_key_exists('class', $handlerValues['formatter'])) {
                $class = new ReflectionClass('\\Monolog\Formatter\\' . $handlerValues['formatter']['class']);
                $formatter = $class->newInstanceArgs($handlerValues['formatter']['args']);
                $handlerWrapper->setFormatter($formatter);
            }

            $logger->pushHandler($handlerWrapper->getHandler());
        }

        return $logger;
    }
}