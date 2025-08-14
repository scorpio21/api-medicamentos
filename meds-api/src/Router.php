<?php

declare(strict_types=1);

namespace App;

class Router {
	/** @var array<int, array{method:string, pattern:string, handler:callable}> */
	private array $routes = [];

	public function addRoute(string $method, string $pattern, callable $handler): void {
		$this->routes[] = [
			'method' => strtoupper($method),
			'pattern' => '#^' . $pattern . '$#',
			'handler' => $handler,
		];
	}

	public function dispatch(string $method, string $path): void {
		$method = strtoupper($method);
		$path = parse_url($path, PHP_URL_PATH) ?? '/';

		foreach ($this->routes as $route) {
			if ($route['method'] !== $method) {
				continue;
			}
			if (preg_match($route['pattern'], $path, $matches)) {
				array_shift($matches); // Drop the full match
				$handler = $route['handler'];
				$handler(...$matches);
				return;
			}
		}

		http_response_code(404);
		echo json_encode(['error' => 'Route not found']);
	}
}