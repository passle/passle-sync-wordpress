<?php

namespace Passle\PassleSync\Utils;

use InvalidArgumentException;

class UrlFactory
{
    private $protocol = "https";
    private $root = "clientwebapi.passle.localhost/api"; // TODO: Don't harcode this URL
    private $path = "/";
    private $parameters = [];

    public function protocol($protocol)
    {
        $this->protocol = $protocol;
        return $this;
    }

    public function root($root)
    {
        $this->root = $root;
        return $this;
    }

    public function path($path)
    {
        $this->path = $path;
        return $this;
    }

    public function parameters($parameters)
    {
        foreach ($parameters as $key => $value) {
            $this->parameters[$key] = $value;
        }

        return $this;
    }

    public function build()
    {
        if (empty($this->root)) {
            throw new InvalidArgumentException("The root address cannot be null or empty");
        }

        $url = "{$this->protocol}://{$this->root}{$this->path}";
        $query = http_build_query($this->parameters, "", "&");

        return join("?", array(
            $url,
            $query
        ));
    }
}
