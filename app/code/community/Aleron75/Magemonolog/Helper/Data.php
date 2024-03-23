<?php
declare(strict_types=1);

final class Aleron75_Magemonolog_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getChannelNameByFile(string $fileName): string
    {
        return basename($fileName, $this->getLogFileExtension());
    }

    private function getLogFileExtension(): string
    {
        return '.' . pathinfo(Mage::getStoreConfig('dev/log/file'), PATHINFO_EXTENSION);
    }
}