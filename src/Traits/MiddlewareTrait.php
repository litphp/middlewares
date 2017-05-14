<?php namespace Lit\Middlewares\Traits;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Class MiddlewareTrait
 * @package Lit\Middlewares\Traits
 *
 * @property ServerRequestInterface $request
 */
trait MiddlewareTrait
{
    /**
     * @param ServerRequestInterface $request
     * @return static
     */
    public static function fromRequest(ServerRequestInterface $request)
    {
        if (!$instance = $request->getAttribute(static::ATTR_KEY)) {
            throw new \RuntimeException('middleware not found:' . static::ATTR_KEY);
        }
        if (!$instance instanceof static) {
            throw new \RuntimeException('middleware class error:' . static::ATTR_KEY);
        }

        return $instance;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     */
    protected function attachToRequest(ServerRequestInterface $request = null)
    {
        $request = $request ?: $this->request;

        if ($request->getAttribute(static::ATTR_KEY)) {
            throw new \RuntimeException('middleware collision:' . static::ATTR_KEY);
        }

        return $this->request = $request->withAttribute(static::ATTR_KEY, $this);
    }
}
