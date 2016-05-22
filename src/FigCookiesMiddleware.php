<?php namespace Lit\Middlewares;

use Dflydev\FigCookies\Cookies;
use Dflydev\FigCookies\SetCookie;
use Dflydev\FigCookies\SetCookies;
use Nimo\AbstractMiddleware;
use Psr\Http\Message\ServerRequestInterface;

class FigCookiesMiddleware extends AbstractMiddleware
{
    const ATTR_KEY = self::class;
    /**
     * @var Cookies
     */
    protected $requestCookies;
    /**
     * @var SetCookies
     */
    protected $responseCookies;

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
     * @return Cookies
     */
    public function getRequestCookies()
    {
        return $this->requestCookies;
    }

    /**
     * @return SetCookies
     */
    public function getResponseCookies()
    {
        return $this->responseCookies;
    }

    public function getRequestCookie($name, $default = null)
    {
        $cookie = $this->requestCookies->get($name);

        return $cookie ? $cookie->getValue() : $default;
    }

    /**
     * @param $name
     * @param mixed $value
     * @param $domain
     * @param $expires
     * @param $httpOnly
     * @param $maxAge
     * @param $path
     * @param $secure
     */
    public function setResponseCookie(
        $name,
        $value,
        $domain = null,
        $expires = null,
        $httpOnly = null,
        $maxAge = null,
        $path = null,
        $secure = null
    )
    {
        if (!$value instanceof SetCookie) {
            if (is_string($value)) {
                $value = SetCookie::create($name, $value);
            } elseif (count($args = func_get_args()) > 2) {
                $value = SetCookie::create($name, $value);
                if (isset($domain)) {
                    $value = $value->withDomain($domain);
                }
                if (isset($expires)) {
                    $value = $value->withExpires($expires);
                }
                if (isset($httpOnly)) {
                    $value = $value->withHttpOnly($httpOnly);
                }
                if (isset($maxAge)) {
                    $value = $value->withMaxAge($maxAge);
                }
                if (isset($path)) {
                    $value = $value->withPath($path);
                }
                if (isset($secure)) {
                    $value = $value->withSecure($secure);
                }
            } elseif (is_array($value)) {
                $arr = $value;
                $value = SetCookie::create($name, $arr['value']);
                if (isset($arr['domain'])) {
                    $value = $value->withDomain($arr['domain']);
                }
                if (isset($arr['expires'])) {
                    $value = $value->withExpires($arr['expires']);
                }
                if (isset($arr['httpOnly'])) {
                    $value = $value->withHttpOnly($arr['httpOnly']);
                }
                if (isset($arr['maxAge'])) {
                    $value = $value->withMaxAge($arr['maxAge']);
                }
                if (isset($arr['path'])) {
                    $value = $value->withPath($arr['path']);
                }
                if (isset($arr['secure'])) {
                    $value = $value->withSecure($arr['secure']);
                }
            }
        }

        $this->responseCookies = $this->responseCookies->with($value);
    }

    public function unsetResponseCookie($name)
    {
        $this->responseCookies = $this->responseCookies->without($name);

        return $this;
    }


    protected function main()
    {
        if ($this->request->getAttribute(static::ATTR_KEY)) {
            throw new \RuntimeException('middleware collision:' . static::ATTR_KEY);
        }

        $this->requestCookies = Cookies::fromRequest($this->request);
        $this->responseCookies = new SetCookies;

        $this->response = $this->next();

        $cookies = SetCookies::fromResponse($this->response);
        foreach ($this->responseCookies->getAll() as $setCookie) {
            $cookies = $cookies->with($setCookie);
        }

        return $cookies->renderIntoSetCookieHeader($this->response);
    }
}