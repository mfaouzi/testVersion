<?php

namespace Aliznet\WCSBundle\Writer\File;

use Akeneo\Bundle\BatchBundle\Job\RuntimeErrorException;
use Pim\Component\Connector\Writer\File\FilePathResolverInterface;
use Pim\Component\Connector\Writer\File\SimpleFileWriter as BaseFileWriter;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Attribute Writer.
 *
 * @author    aliznet
 * @copyright 2016 ALIZNET (www.aliznet.fr)
 */
class AttributeWriter extends BaseFileWriter
{
    /**
     * @Assert\NotBlank
     * @Assert\Choice(choices={",", ";", "|"}, message="The value must be one of , or ; or |")
     *
     * @var string
     */
    protected $delimiter = ';';

    /**
     * @Assert\NotBlank
     * @Assert\Choice(choices={"""", "'"}, message="The value must be one of "" or '")
     *
     * @var string
     */
    protected $enclosure = '"';

    /**
     * @var bool
     */
    protected $withHeader = true;

    /**
     * @var array
     */
    protected $writtenFiles = array();

    /**
     * @var array
     */
    protected $items = [];

    /**
     * Set the csv delimiter character.
     *
     * @param string $delimiter
     */
    public function setDelimiter($delimiter)
    {
        $this->delimiter = $delimiter;
    }

    /**
     * Get the csv delimiter character.
     *
     * @return string
     */
    public function getDelimiter()
    {
        return $this->delimiter;
    }

    /**
     * Set the csv enclosure character.
     *
     * @param string $enclosure
     */
    public function setEnclosure($enclosure)
    {
        $this->enclosure = $enclosure;
    }

    /**
     * Get the csv enclosure character.
     *
     * @return string
     */
    public function getEnclosure()
    {
        return $this->enclosure;
    }

    /**
     * Set whether or not to print a header row into the csv.
     *
     * @param bool $withHeader
     */
    public function setWithHeader($withHeader)
    {
        $this->withHeader = $withHeader;
    }

    /**
     * Get whether or not to print a header row into the csv.
     *
     * @return bool
     */
    public function isWithHeader()
    {
        return $this->withHeader;
    }

    /**
     * @return array
     */
    public function getWrittenFiles()
    {
        return $this->writtenFiles;
    }

    /**
     * @param FilePathResolverInterface $filePathResolver
     */
    public function __construct(FilePathResolverInterface $filePathResolver)
    {
        parent::__construct($filePathResolver);
    }

    /**
     * Flush items into a csv file.
     *
     * @throws RuntimeErrorException
     */
    public function flush()
    {
        $this->writtenFiles[$this->getPath()] = basename($this->getPath());
        $uniqueKeys = $this->getAllKeys($this->items);
        $fullItems = $this->mergeKeys($uniqueKeys);
        if (false === $csvFile = fopen($this->getPath(), 'w')) {
            throw new RuntimeErrorException('Failed to open file %path%', ['%path%' => $this->getPath()]);
        }
        $header = $this->isWithHeader() ? $uniqueKeys : [];
        if (false === fputcsv($csvFile, $header, $this->delimiter)) {
            throw new RuntimeErrorException('Failed to write to file %path%', ['%path%' => $this->getPath()]);
        }
        foreach ($fullItems as $item) {
            if (false === fputcsv($csvFile, $item, $this->delimiter, $this->enclosure)) {
                throw new RuntimeErrorException('Failed to write to file %path%', ['%path%' => $this->getPath()]);
            } elseif ($this->stepExecution) {
                $this->stepExecution->incrementSummaryInfo('write');
            }
        }
    }

    /**
     * @return array
     */
    public function getConfigurationFields()
    {
        return
                array_merge(parent::getConfigurationFields(), array(
            'delimiter' => array(
                'options' => array(
                    'label' => 'pim_base_connector.export.delimiter.label',
                    'help'  => 'pim_base_connector.export.delimiter.help',
                ),
            ),
            'enclosure' => array(
                'options' => array(
                    'label' => 'pim_base_connector.export.enclosure.label',
                    'help'  => 'pim_base_connector.export.enclosure.help',
                ),
            ),
            'withHeader' => array(
                'type'    => 'switch',
                'options' => array(
                    'label' => 'pim_base_connector.export.withHeader.label',
                    'help'  => 'pim_base_connector.export.withHeader.help',
                ),
            ), ));
    }

    /**
     * @param array $items
     */
    public function write(array $items)
    {
        if (!is_dir(dirname($this->getPath()))) {
            mkdir(dirname($this->getPath()), 0777, true);
        }
        $this->items = array_merge($this->items, $items);
    }

    /**
     * Get a set of all keys inside arrays.
     *
     * @param array $items
     *
     * @return array
     */
    protected function getAllKeys(array $items)
    {
        $intKeys = [];
        foreach ($items as $itemss) {
            foreach ($itemss as $item) {
                $intKeys[] = array_keys($item);
            }
        }
        if (0 === count($intKeys)) {
            return [];
        }
        $mergedKeys = call_user_func_array('array_merge', $intKeys);

        return array_unique($mergedKeys);
    }

    /**
     * Merge the keys in arrays.
     *
     * @param array $uniqueKeys
     *
     * @return array
     */
    protected function mergeKeys($uniqueKeys)
    {
        $uniqueKeys = array_fill_keys($uniqueKeys, '');
        $fullItems = [];
        foreach ($this->items as $itemss) {
            foreach ($itemss as $item) {
                $fullItems[] = array_merge($uniqueKeys, $item);
            }
        }

        return $fullItems;
    }
}
