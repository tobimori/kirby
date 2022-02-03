<?php

namespace Kirby\Form;

use Kirby\Cms\Field;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Toolkit\Collection;
use Kirby\Toolkit\Obj;
use Kirby\Toolkit\Query;
use Kirby\Toolkit\Str;

/**
 * Option Queries are run against any set
 * of data. In case of Kirby, you can query
 * pages, files, users or structures to create
 * options out of them.
 *
 * @package   Kirby Form
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class OptionsQuery extends OptionsSource
{

    /**
     * Class aliases of predefined Kirby objects
     *
     * @var array
     */
    protected $aliases = [];

    /**
     * Query syntax string
     *
     * @var string
     */
    protected $query;

    /**
     * Returns class aliases of predefined Kirby objects
     * @return array
     */
    public function aliases(): array
    {
        return $this->aliases;
    }

    /**
     * Resolves field value
     *
     * @param string $field
     * @param string $object
     * @return string
     * @throws \Kirby\Exception\NotFoundException
     */
    protected function field(string $field, string $object)
    {
        $value = $this->$field();

        if (is_array($value) === true) {
            if (isset($value[$object]) === false) {
                throw new NotFoundException('Missing "' . $field . '" definition');
            }

            $value = $value[$object];
        }

        return $value;
    }

    /**
     * Get options from resolved query syntax
     *
     * @return array
     */
    public function options(): array
    {
        // prefer cached options
        if (is_array($this->options) === true) {
            return $this->options;
        }

        $data    = $this->data();
        $query   = new Query($this->query(), $data);
        $result  = $query->result();
        $result  = $this->resultToCollection($result);
        $options = [];

        foreach ($result as $item) {
            $alias = $this->resolve($item);
            $data  = array_merge($data, [$alias => $item]);

            $options[] = [
                'text'  => Str::safeTemplate(
                    $this->field('text', $alias),
                    $data
                ),
                'value' => Str::template(
                    $this->field('value', $alias),
                    $data
                )
            ];
        }

        return $this->options = $options;
    }

    /**
     * @return string
     */
    public function query(): string
    {
        return $this->query;
    }

    /**
     * @param $object
     * @return mixed|string|null
     */
    public function resolve($object)
    {
        // fast access
        if ($alias = ($this->aliases[get_class($object)] ?? null)) {
            return $alias;
        }

        // slow but precise resolving
        foreach ($this->aliases as $className => $alias) {
            if (is_a($object, $className) === true) {
                return $alias;
            }
        }

        return 'item';
    }

    /**
     * @param $result
     * @throws \Kirby\Exception\InvalidArgumentException
     */
    protected function resultToCollection($result)
    {
        if (is_array($result)) {
            foreach ($result as $key => $item) {
                if (is_scalar($item) === true) {
                    $result[$key] = new Obj([
                        'key'   => new Field(null, 'key', $key),
                        'value' => new Field(null, 'value', $item),
                    ]);
                }
            }

            $result = new Collection($result);
        }

        if (is_a($result, 'Kirby\Toolkit\Collection') === false) {
            throw new InvalidArgumentException('Invalid query result data');
        }

        return $result;
    }

    /**
     * @param array|null $aliases
     * @return $this
     */
    protected function setAliases(?array $aliases = null)
    {
        $this->aliases = $aliases;
        return $this;
    }

    /**
     * @param string $query
     * @return $this
     */
    protected function setQuery(string $query)
    {
        $this->query = $query;
        return $this;
    }
}
