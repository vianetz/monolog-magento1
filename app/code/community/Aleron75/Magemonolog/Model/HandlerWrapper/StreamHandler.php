<?php
declare(strict_types=1);

use Monolog\Handler\StreamHandler;

class Aleron75_Magemonolog_Model_HandlerWrapper_StreamHandler extends Aleron75_Magemonolog_Model_HandlerWrapper_AbstractHandler
{
    public function __construct(array $args)
    {
        $this->_validateArgs($args);
        $this->_handler = new StreamHandler(
            $args['stream'],
            $args['level'],
            $args['bubble'],
            $args['filePermission'],
            $args['useLocking']
        );
    }

    protected function _validateArgs(array &$args)
    {
        parent::_validateArgs($args);

        // Stream
        $file = $args['stream'] ?? Mage::getStoreConfig('dev/log/file');
        $args['stream'] = Mage::getBaseDir('var') . DS . 'log' . DS . $file;

        // File Permission
        $filePermission = null;
        if (isset($args['filePermission']) && is_numeric($args['filePermission'])) {
            $filePermission = filter_var($args['filePermission'], FILTER_VALIDATE_INT);
        }

        $args['filePermission'] = $filePermission;

        // Use Locking
        $useLocking = false;
        if (isset($args['useLocking'])) {
            $useLocking = filter_var($args['useLocking'], FILTER_VALIDATE_BOOLEAN);
        }

        $args['useLocking'] = $useLocking;
    }
}