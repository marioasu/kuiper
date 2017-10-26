<?php

namespace kuiper\web;

trait RouteRegistarTrait
{
    /**
     * @var array
     */
    private $routes = [];

    /**
     * @var array
     */
    private $groupStack = [];

    /**
     * @var string
     */
    private $routeClass = Route::class;

    /**
     * @var string
     */
    private $actionDelimiter = ':';

    /**
     * @var string
     */
    private $namespace;

    /**
     * Add GET route.
     *
     * @param string          $pattern The route URI pattern
     * @param callable|string $action  The route callback routine
     *
     * @return RouteInterface
     */
    public function get($pattern, $action)
    {
        return $this->map(['GET'], $pattern, $action);
    }

    /**
     * Add POST route.
     *
     * @param string          $pattern The route URI pattern
     * @param callable|string $action  The route callback routine
     *
     * @return RouteInterface
     */
    public function post($pattern, $action)
    {
        return $this->map(['POST'], $pattern, $action);
    }

    /**
     * Add PUT route.
     *
     * @param string          $pattern The route URI pattern
     * @param callable|string $action  The route callback routine
     *
     * @return RouteInterface
     */
    public function put($pattern, $action)
    {
        return $this->map(['PUT'], $pattern, $action);
    }

    /**
     * Add PATCH route.
     *
     * @param string          $pattern The route URI pattern
     * @param callable|string $action  The route callback routine
     *
     * @return RouteInterface
     */
    public function patch($pattern, $action)
    {
        return $this->map(['PATCH'], $pattern, $action);
    }

    /**
     * Add DELETE route.
     *
     * @param string          $pattern The route URI pattern
     * @param callable|string $action  The route callback routine
     *
     * @return RouteInterface
     */
    public function delete($pattern, $action)
    {
        return $this->map(['DELETE'], $pattern, $action);
    }

    /**
     * Add OPTIONS route.
     *
     * @param string          $pattern The route URI pattern
     * @param callable|string $action  The route callback routine
     *
     * @return RouteInterface
     */
    public function options($pattern, $action)
    {
        return $this->map(['OPTIONS'], $pattern, $action);
    }

    /**
     * Add route for any HTTP method.
     *
     * @param string          $pattern The route URI pattern
     * @param callable|string $action  The route callback routine
     *
     * @return RouteInterface
     */
    public function any($pattern, $action)
    {
        return $this->map(['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'], $pattern, $action);
    }

    /**
     * Add route with multiple methods.
     *
     * @param string[]        $methods Numeric array of HTTP method names
     * @param string          $pattern The route URI pattern
     * @param callable|string $action  The route callback routine
     *
     * @return RouteInterface
     */
    public function map(array $methods, $pattern, $action)
    {
        if (empty($action)) {
            throw new \InvalidArgumentException('route callback must not be empty');
        }
        $attributes = [];
        if (!empty($this->groupStack)) {
            $attributes = end($this->groupStack);
        }
        $namespace = $this->namespace;
        if (isset($attributes['namespace'])) {
            $namespace = $attributes['namespace'];
            unset($attributes['namespace']);
        }
        $route = new $this->routeClass($methods, $pattern, $this->parseAction($action, $namespace));
        if (!empty($attributes)) {
            $route->match($attributes);
        }
        $this->routes[] = $route;

        return $route;
    }

    public function group(array $attributes, \Closure $callback)
    {
        if (!empty($this->groupStack)) {
            $attributes = array_merge(end($this->groupStack), $attributes);
        }

        $this->groupStack[] = $attributes;
        $callback($this);

        array_pop($this->groupStack);
    }

    public function getRoutes()
    {
        return $this->routes;
    }

    public function setRouteClass($routeClass)
    {
        $this->routeClass = $routeClass;

        return $this;
    }

    public function setDefaultNamespace($ns)
    {
        $this->namespace = $ns;

        return $this;
    }

    public function setActionDelimiter($actionDelimiter)
    {
        $this->actionDelimiter = $actionDelimiter;

        return $this;
    }

    protected function parseAction($action, $namespace)
    {
        if (is_string($action)) {
            if (($pos = strpos($action, $this->actionDelimiter)) !== false) {
                $callback = [
                    $this->addNamespace(substr($action, 0, $pos), $namespace),
                    substr($action, $pos + strlen($this->actionDelimiter)) ?: null,
                ];
            } else {
                $callback = $action;
            }
        } elseif (is_array($action)) {
            if (isset($action['controller'])) {
                $callback = [
                    is_string($action['controller']) ? $this->addNamespace($action['controller'], $name) : $action['controller'],
                    isset($action['action']) ? $action['action'] : null,
                ];
            } elseif (isset($action[0])) {
                $callback = [
                    is_string($action[0]) ? $this->addNamespace($action[0], $name) : $action[0],
                    isset($action[1]) ? $action[1] : null,
                ];
            } else {
                throw new \InvalidArgumentException('Invalid action '.gettype($action));
            }
        } elseif (is_callable($action)) {
            $callback = $action;
        } else {
            throw new \InvalidArgumentException('Invalid action '.gettype($action));
        }

        return $callback;
    }

    protected function addNamespace($class, $namespace)
    {
        if (empty($class)) {
            throw new \InvalidArgumentException('Invalid action controller');
        }
        if (empty($namespace) || $class[0] == '\\') {
            return $class;
        }

        return rtrim($namespace, '\\').'\\'.$class;
    }
}
