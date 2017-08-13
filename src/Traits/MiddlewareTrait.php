<?php namespace Lit\Middlewares\Traits;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Class MiddlewareTrait
 * @package Lit\Middlewares\Traits
 */
trait MiddlewareTrait
{
    /**
     * @param ServerRequestInterface $request
     * @return static
     */
    public static function fromRequest(ServerRequestInterface $request): self
    {
        $key = defined('static::ATTR_KEY') ? static::ATTR_KEY : static::class;
        if (!$instance = $request->getAttribute($key)) {
            throw new \RuntimeException('middleware not found:' . $key);
        }
        if (!$instance instanceof static) {
            throw new \RuntimeException('middleware class error:' . $key);
        }

        return $instance;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     */
    protected function attachToRequest(ServerRequestInterface $request = null)
    {
        /**
         * @var ServerRequestInterface $request
         */
        $request = $request ?: $this->request;

        $key = defined('static::ATTR_KEY') ? static::ATTR_KEY : static::class;
        if ($request->getAttribute($key)) {
            throw new \RuntimeException('middleware collision:' . $key);
        }

        return $this->request = $request->withAttribute($key, $this);
    }
}
