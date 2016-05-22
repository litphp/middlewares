<?php namespace Lit\Middlewares\Traits;

use Psr\Http\Message\ServerRequestInterface;

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

    protected function attachToRequest()
    {
        if ($this->request->getAttribute(static::ATTR_KEY)) {
            throw new \RuntimeException('middleware collision:' . static::ATTR_KEY);
        }
        $this->request = $this->request->withAttribute(static::ATTR_KEY, $this);
    }
}
