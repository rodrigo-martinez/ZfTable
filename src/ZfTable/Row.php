<?php
/**
 * ZfTable ( Module for Zend Framework 2)
 *
 * @copyright Copyright (c) 2013 Piotr Duda dudapiotrek@gmail.com
 * @license   MIT License
 */

namespace ZfTable;

use ZfTable\AbstractElement;
use ZfTable\Decorator\DecoratorFactory;
use ZfTable\Table\Exception;

class Row extends AbstractElement
{
    protected $class = array('zf-data-row');

    /**
     *
     * @var array
     */
    protected $actualRow;


    /**
     *
     * @param AbstractTable $table
     */
    public function __construct($table)
    {
        $this->table = $table;
    }

    /**
     *
     * @param string $name
     * @param array $options
     * @return Decorator\Header\AbstractHeaderDecorator
     */
    public function addDecorator($name, $options = array())
    {
        $decorator = DecoratorFactory::factoryRow($name, $options);
        $this->attachDecorator($decorator);
        $decorator->setRow($this);
        return $decorator;
    }

    /**
     * Get actual row
     *
     * @return array
     */
    public function getActualRow()
    {
        return $this->actualRow;
    }

    /**
     *
     * @param array $actualRow
     */
    public function setActualRow($actualRow)
    {
        $this->actualRow = $actualRow;
    }


    /**
     * Rendering all rows for table
     *
     * @param string $type html, json, array
     * @return string | array
     */
    public function renderRows($type = 'html')
    {
        if ($type == 'html') {
            return $this->renderRowHtml();
        } elseif ($type == 'array') {
            return $this->renderRowArray();
        } elseif ($type == 'array_assc') {
            return $this->renderRowArray('assc');
        } else {
            throw new Exception\InvalidArgumentException();
        }

    }

    /**
     * Rendering rows as array
     *
     * @param string $type
     * @return array
     */
    private function renderRowArray($type = 'normal')
    {
        $data = $this->getTable()->getData();
        $headers = $this->getTable()->getHeaders();
        $render = array();

        foreach ($data as $rowData) {
            $this->setActualRow($rowData);
            $temp = array();
            foreach ($headers as $name => $options) {
                if ($type == 'assc') {
                    $temp[$name] =  $this->getTable()->getHeader($name)->getCell()->render('array');
                } else {
                    $temp[] =  $this->getTable()->getHeader($name)->getCell()->render('array');
                }
            }
            $render[] = $temp;
        }
        return $render;
    }

    /**
     * rendering row as a html
     *
     * @return string
     */
    private function renderRowHtml()
    {
        $data = $this->getTable()->getData();
        $headers = $this->getTable()->getHeaders();
        $render = '';

        foreach ($data as $rowData) {
            $this->setActualRow($rowData);
            $rowRender = '';

            foreach ($headers as $name => $options) {
                $rowRender .= $this->getTable()->getHeader($name)->getCell()->render('html');
            }

            foreach ($this->decorators as $decorator) {
            	if ($decorator->validConditions()) {			
					$decorator->render('');
				}
            }

            $render .= sprintf('<tr %s>%s</tr>', $this->getAttributes(), $rowRender);
            $this->clearVar();
        }
        return $render;
    }
}
