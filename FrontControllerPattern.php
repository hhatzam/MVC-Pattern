<?php

class Request {

  public function __construct($uri, $params) {
    $this->uri = $uri;
    $this->params = $params;
  }

  public function getUri() {
    return $this->uri;
  }

  public function setParam($key, $value) {
    $this->params[$key] = $value;
    return $this;
  }

  public function getParam($key) {
    if (!isset($this->params[$key])) {
      throw new \InvalidArgumentException("The request parameter with key '$key' is invalid.");
    }
    return $this->params[$key];
  }

  public function getParams() {
    return $this->params;
  }
}

class Response {
  public function __construct($version) {
    $this->version = $version;
  }

  public function getVersion() {
    return $this->version;
  }

  public function addHeader($header) {
    $this->headers[] = $header;
    return $this;
  }

  public function addHeaders(array $headers) {
    foreach ($headers as $header) {
      $this->addHeader($header);
    }
    return $this;
  }

  public function getHeaders() {
    return $this->headers;
  }

  public function send() {
    if (!headers_sent()) {
      foreach($this->headers as $header) {
        header("$this->version $header", true);
      }
    }
  }
}


class Route {

  public function __construct($path, $controllerClass) {
    $this->path = $path;
    $this->controllerClass = $controllerClass;
  }

  public function match(RequestInterface $request) {
    return $this->path === $request->getUri();
  }

  public function createController() {
   return new $this->controllerClass;
  }
}


class Router {
  public function __construct($routes) {
    $this->addRoutes($routes);
  }

  public function addRoute(RouteInterface $route) {
    $this->routes[] = $route;
    return $this;
  }

  public function addRoutes(array $routes) {
    foreach ($routes as $route) {
      $this->addRoute($route);
    }
    return $this;
  }

  public function getRoutes() {
    return $this->routes;
  }

  public function route(RequestInterface $request, ResponseInterface $response) {
    foreach ($this->routes as $route) {
      if ($route->match($request)) {
        return $route;
      }
    }
    $response->addHeader("404 Page Not Found")->send();
    throw new \OutOfRangeException("No route matched the given URI.");
  }
}


class Dispatcher {

  public function dispatch($route, $request, $response)
    $controller = $route->createController();
    $controller->execute($request, $response);
  }
}


class FrontController {

  public function __construct($router, $dispatcher) {
    $this->router = $router;
    $this->dispatcher = $dispatcher;
  }

  public function run(RequestInterface $request, ResponseInterface $response) {
    $route = $this->router-&gt;route($request, $response);
    $this->dispatcher->dispatch($route, $request, $response);
  }
}


$request = new Request("http://example.com/test/");

$response = new Response;

$route1 = new Route("http://example.com/test/", "Acme\\Library\\Controller\\TestController");

$route2 = new Route("http://example.com/error/", "Acme\\Library\\Controller\\ErrorController");

$router = new Router(array($route1, $route2));

$dispatcher = new Dispatcher;

$frontController = new FrontController($router, $dispatcher);

$frontController->run($request, $response);
