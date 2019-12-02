<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


class Amasty_Rules_Model_SampleData
{
    protected $filePath = 'Sample/Data.csv';

    protected $tableName;

    protected $extensionName;

    protected $table = false;

    protected $write = false;

    protected $install = false;

    public function __construct($options = array())
    {
        if (count($options) > 1) {
            isset($options['install']) ? $install = 1 : $install = 0;
            $this->tableName = $options['tableName'];
            $this->install = $install;
            $this->extensionName = $options['extensionName'];
        }
    }

    /**
     * @param string $filePath
     */
    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;
    }

    public function import()
    {
        if ($this->install) {
            $this->importCsv();
        }
    }

    protected function importCsv()
    {
        $io = new Varien_Io_File();

        $moduleDir = Mage::getModuleDir('root', $this->extensionName);

        $info = pathinfo($moduleDir . DS . $this->filePath);
        $io->open(array('path' => $info['dirname']));
        $io->streamOpen($info['basename'], 'r');

        // check and skip headers
        $headers = $io->streamReadCsv();
        if ($headers === false) {
            $io->streamClose();
            Mage::throwException(Mage::helper('amrules')->__('Can\'t read headers'));
        }

        while (false !== ($csvLine = $io->streamReadCsv())) {
            if (empty($csvLine)) {
                continue;
            }
            $importData = array();
            //NULL date fields
            $csvLine[5] = NULL;
            $csvLine[6] = NULL;
            //replace comma in sku
            $csvLine[7] = str_replace('|',',',$csvLine[7]);
            //replace comma in actions-conditions
            $csvLine[9] = str_replace('|',',',$csvLine[9]);
            $csvLine[10] = str_replace('|',',',$csvLine[10]);
            $importData[] = $csvLine;
            $this->insertArray($headers, $importData);
        }
    }

    protected function insertArray($headers, $importData)
    {
        if (!$this->write) {
            $this->write = Mage::getSingleton("core/resource")->getConnection("core_write");
            $this->table = Mage::getSingleton("core/resource")->getTableName($this->tableName);
        }

        $this->write->insertArray($this->table, $headers, $importData);
    }
    
}
