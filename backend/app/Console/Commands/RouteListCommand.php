<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use \Laravel\Lumen\Routing\Router;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Closure;

/**
 * This is used to create Laravel-like route:list command
 * to display all registered routes in table/list just like in Laravel
 * 
 * Most of the code comes from https://github.com/laravel/framework/blob/8.x/src/Illuminate/Foundation/Console/RouteListCommand.php
 */
class RouteListCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'route:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display all registered routes in table/list just like in Laravel';

    /**
     * The router instance.
     *
     * @var \Illuminate\Routing\Router
     */
    protected $router;
    
    /**
     * An array of all the registered routes.
     */
    protected $routes = [];

    /**
     * The table headers for the command.
     *
     * @var array
     */
    protected $headers = ['Method', 'URI', 'Name', 'Action', 'Middleware'];

    /**
     * The columns to display when using the "compact" flag.
     *
     * @var string[]
     */
    protected $compactColumns = ['method', 'uri', 'action'];

    /**
     * Constructor
     */
    public function __construct(Router $router)
    {
        parent::__construct();

        $this->router = $router;
        $this->routes = $router->getRoutes();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if (count($this->routes) == 0) {
            return $this->error("Your application doesn't have any routes.");
        }

        $this->displayRoutes($this->getRoutes());
    }

    /**
     * Compile the routes into a displayable format.
     *
     * @return array
     */
    protected function getRoutes()
    {
        $routes = collect($this->routes)->map(function ($route) {
            return $this->getRouteInformation($route);
        })->all();

        return $this->pluckColumns($routes);
    }


    /**
     * Get the route information for a given route.
     *
     * @param  $route
     * @return array
     */
    protected function getRouteInformation($route)
    {
        return [
            'method' => $route['method'],
            'uri'    => $route['uri'],
            'name'   => $this->getRouteName($route),
            'action' => $this->getRouteActionName($route),
            'middleware' => $this->getMiddleware($route),
        ];
    }

    protected function displayRoutes($routes)
    {
        if ($this->option('json')) {
            $this->line(json_encode(array_values($routes)));

            return;
        }

        $this->table($this->headers, $routes);
    }


    /**
     * Remove unnecessary columns from the routes.
     *
     * @param  array  $routes
     * @return array
     */
    protected function pluckColumns(array $routes)
    {
        return array_map(function ($route) {
            return Arr::only($route, $this->getColumns());
        }, $routes);
    }


    /**
     * Get the domain defined for the route.
     *
     * @return string|null
     */
    public function getDomain($route)
    {
        return isset($route['action']['domain'])
                ? str_replace(['http://', 'https://'], '', $route['action']['domain']) : null;
    }

    /**
     * Get the route name.
     *
     * @param  $route
     * @return string
     */
    protected function getRouteName($route)
    {
        return (isset($route['action']['as'])) ? $route['action']['as'] : '';
    }

    /**
     * Get the route action name.
     *
     * @param  $route
     * @return string
     */
    protected function getRouteActionName($route)
    {
        if(!isset($route['action']['uses'])) {
            return "Closure";
        } else {
            return "Controller";
        }
    }

    /**
     * Get the middleware for the route.
     *
     * @param  $route
     * @return string
     */
    protected function getMiddleware($route)
    {
        if (isset($route['action']['middleware'])) {
            return join(',', $route['action']['middleware']);
        }
        return '';
    }

    /**
     * Get the column names to show (lowercase table headers).
     *
     * @return array
     */
    protected function getColumns()
    {
        $availableColumns = array_map('strtolower', $this->headers);

        if ($this->option('compact')) {
            return array_intersect($availableColumns, $this->compactColumns);
        }

        if ($columns = $this->option('columns')) {
            return array_intersect($availableColumns, $this->parseColumns($columns));
        }

        return $availableColumns;
    }

    /**
     * Parse the column list.
     *
     * @param  array  $columns
     * @return array
     */
    protected function parseColumns(array $columns)
    {
        $results = [];
        foreach ($columns as $i => $column) {
            if (Str::contains($column, ',')) {
                $results = array_merge($results, explode(',', $column));
            } else {
                $results[] = $column;
            }
        }

        return array_map('strtolower', $results);
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['columns', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Columns to include in the route table'],
            ['compact', 'c', InputOption::VALUE_NONE, 'Only show method, URI and action columns'],
            ['json', null, InputOption::VALUE_NONE, 'Output the route list as JSON'],
            ['method', null, InputOption::VALUE_OPTIONAL, 'Filter the routes by method'],
            ['name', null, InputOption::VALUE_OPTIONAL, 'Filter the routes by name'],
            ['path', null, InputOption::VALUE_OPTIONAL, 'Filter the routes by path'],
            ['reverse', 'r', InputOption::VALUE_NONE, 'Reverse the ordering of the routes'],
            ['sort', null, InputOption::VALUE_OPTIONAL, 'The column (domain, method, uri, name, action, middleware) to sort by', 'uri'],
        ];
    }

}