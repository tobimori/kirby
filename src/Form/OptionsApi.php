<?php

namespace Kirby\Form;

use Kirby\Cms\Nest;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Http\Remote;
use Kirby\Http\Url;
use Kirby\Toolkit\Query;
use Kirby\Toolkit\Str;

/**
 * The OptionsApi class handles fetching options
 * from any REST API with valid JSON data.
 *
 * @package   Kirby Form
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class OptionsApi extends OptionsSource
{

    /**
     * Query syntax string
     *
     * @var string|null
     */
    protected $fetch;

    /**
     * API endpoint URL
     *
     * @var string
     */
    protected $url;

    /**
     * Returns query syntax string
     *
     * @return mixed
     */
    public function fetch()
    {
        return $this->fetch;
    }

    /**
     * Gets options from API
     *
     * @return array
     * @throws \Exception
     * @throws \Kirby\Exception\InvalidArgumentException
     */
    public function options(): array
    {
        // prefer cached options
        if (is_array($this->options) === true) {
            return $this->options;
        }

        if (Url::isAbsolute($this->url()) === true) {
            // URL, request via cURL
            $data = Remote::get($this->url())->json();
        } else {
            // local file, get contents locally

            // ensure the file exists before trying to load it as the
            // file_get_contents() warnings need to be suppressed
            if (is_file($this->url()) !== true) {
                throw new Exception('Local file ' . $this->url() . ' was not found');
            }

            $content = @file_get_contents($this->url());

            if (is_string($content) !== true) {
                throw new Exception('Unexpected read error'); // @codeCoverageIgnore
            }

            if (empty($content) === true) {
                return [];
            }

            $data = json_decode($content, true);
        }

        if (is_array($data) === false) {
            throw new InvalidArgumentException('Invalid options format');
        }

        $result  = (new Query($this->fetch(), Nest::create($data)))->result();
        $options = [];

        foreach ($result as $item) {
            $data = array_merge($this->data(), ['item' => $item]);

            $options[] = [
                'text'  => Str::safeTemplate($this->text(), $data),
                'value' => Str::template($this->value(), $data)
            ];
        }

        return $options;
    }

    /**
     * @param string|null $fetch
     * @return $this
     */
    protected function setFetch(?string $fetch = null)
    {
        $this->fetch = $fetch;
        return $this;
    }

    /**
     * @param string $url
     * @return $this
     */
    protected function setUrl(string $url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function url(): string
    {
        return Str::safeTemplate($this->url, $this->data());
    }
}
