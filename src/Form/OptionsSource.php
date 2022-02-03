<?php

namespace Kirby\Form;

use Kirby\Toolkit\Properties;


abstract class OptionsSource
{
    use Properties;

    /**
     * Data array for template strings
     *
     * @var array
     */
    protected $data;

    /**
     * Cache for options
     *
     * @var array|string|null
     */
    protected $options;

    /**
     * Query syntax for option `text` key
     *
     * @var mixed
     */
    protected $text;

    /**
     * Query syntax for option `value` key
     *
     * @var mixed
     */
    protected $value;

    /**
     * Returns data array for template strings
     *
     * @return array
     */
    public function data(): array
    {
        return $this->data;
    }

    /**
     * @return array
     */
    abstract public function options(): array;

    /**
     * @param array $data
     * @return $this
     */
    protected function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @param array|string|null $options
     * @return $this
     */
    protected function setOptions($options = null)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @param string $text
     * @return $this
     */
    protected function setText(?string $text = null)
    {
        $this->text = $text;
        return $this;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    protected function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return mixed
     */
    public function text()
    {
        return $this->text;
    }

    /**
     * @return mixed
     */
    public function value()
    {
        return $this->value;
    }

    /**
     * Returns options
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->options();
    }
}
