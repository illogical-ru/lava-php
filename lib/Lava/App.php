<?php

/*!
 * https://github.com/illogical-ru/lava-php
 *
 * Copyright illogical
 * Released under the MIT license
 */

namespace Lava;

use Lava\Stash;
use Lava\Env;
use Lava\Args;
use Lava\Cookie;
use Lava\Session;
use Lava\Safe;
use Lava\Route;
use Lava\Validator;


class App {

    public  $conf,
            $stash,
            $env, $args, $cookie, $session,
            $safe;

    private $routes = [];

    static
    private $types  = [
                   'application/octet-stream',
        'txt'   => 'text/plain',
        'html'  => 'text/html',
        'js'    => 'text/javascript',
        'json'  => 'application/json',
        'jsonp' => 'application/javascript',
    ];


    public function __construct ($conf = NULL) {

        $this->conf    = new Stash ($conf);
        $this->stash   = new Stash;
        $this->env     = new Env;
        $this->args    = new Args;
        $this->cookie  = new Cookie;
        $this->session = new Session;
        $this->safe    = new Safe  ($this->conf->safe());

        if (method_exists($this, 'start')) {
            $this->start();
        }
    }


    public function host ($scheme = NULL, $subdomain = NULL) {

        $host = $this->conf->host && $subdomain !== TRUE
            ?   $this->conf->host
            :   $this->env ->host;

        if ($scheme === TRUE) {
            $scheme = $this->env->is_https ? 'https' : 'http';
        }
        if ($subdomain && $subdomain !== TRUE) {
            $host   = join('.', array_merge(
                (array)$subdomain, [$host]
            ));
        }
        if ($scheme) {
            $host   = "${scheme}://${host}";
        }

        return $host;
    }

    public function home ($path = NULL) {

        $home = $this->conf->home
            ?   $this->conf->home
            :   getcwd();

        return join('/', array_merge([$home], (array)$path));
    }

    public function pub ($path = NULL) {

        $pub  = $this->conf->pub
            ?   $this->conf->pub
            :   preg_replace('|/[^/]*$|', '', $this->env->script);

        return join('/', array_merge([$pub], (array)$path));
    }

    public function uri ($uri = NULL, $data = NULL, $append = FALSE) {

        if     (! isset($uri)) {
            $uri  = $this->env->uri;
        }
        elseif (  isset($this->routes[$uri])) {
            $uri  = $this->routes[$uri]->uri($data);
        }
        elseif (! preg_match('|^(?:[a-z]+:/)?/|i', $uri)) {
            $uri  = rtrim($this->env->uri, '/') . '/' . $uri;
        }

        if     (  $data || $append) {

            $data = $this->args->_query($data, $append);

            if ($data) {
                $uri .= (strpos($uri, '?') ? '&' : '?') . $data;
            }
        }

        return $uri;
    }

    public function url () {

        $args      = func_get_args();

        $subdomain = key_exists(3, $args) ? $args[3] : TRUE;
        $url       = call_user_func_array([$this, 'uri'], $args);

        if (! preg_match('|^[a-z]+://|i', $url)) {
            $url   = $this->host(TRUE, $subdomain) . $url;
        }

        return $url;
    }
    public function url_ref_or () {

        $env = $this->env;

        if   (  preg_match(
                    '|^[a-z]+://([^/]+)([^?]*)|i', $env->referer, $match
                )
            && (strcasecmp($env->host, $match[1]) || $env->uri != $match[2])
        )
        {
            return $env->referer;
        }
        else {
            return call_user_func_array([$this, 'url'], func_get_args());
        }
    }

    public function type () {

        if     (! $this->env->is_rewrite) {
            $type = $this->args->type;
        }
        elseif (  preg_match('/\.(\w+)$/', $this->env->uri, $match)) {
            $type = end($match);
        }

        if     (! isset($type)) {
            $type = $this->conf->type;
        }

        return strtolower($type);
    }

    public function render ($handler) {

        $type     = $this->type();
        $callback = $this->args->callback;

        if     (  isset($handler[$type])) {
            $case =     $handler[$type];
        }
        elseif (  isset($handler[    0])) {
            $case =     $handler[    0];
        }
        else   {
            return;
        }

        if     (  $type == 'json' && $callback) {
            $type = 'jsonp';
        }

        if     (! headers_sent()) {

            if   (isset(self::$types[$type])) {

                $content_type = self::$types[$type];

                if ($this->conf->charset) {
                    $content_type .= '; charset=' . $this->conf->charset;
                }
            }
            else {
                $content_type = self::$types[0];
            }

            header("Content-Type: ${content_type}");
            header('Expires: 0');
            header('Cache-Control: no-store, no-cache, must-revalidate');
            header('Pragma: no-cache');
        }

        $data     = is_object($case) && is_callable($case)
            ? call_user_func ($case, $this)
            :                 $case;

        if     (  isset($data)) {

            if (preg_match('/^jsonp?$/', $type)) {
                $data = json_encode($data);
            }
            if ($type == 'jsonp') {
                $data = "${callback}(${data});";
            }

            echo $data;
        }

        return TRUE;
    }

    public function redirect () {

        $location = call_user_func_array(
            [$this, 'url'], func_get_args()
        );

        return $this->render([
            'json' => ['location' =>  $location],
            function() use ($location) {
                header('Location: ' . $location, TRUE, 302);
            },
        ]);
    }

    public function route       ($rule = '', $cond = NULL) {

        if (strpos($rule, '/') !== 0) {
            $rule = $this->pub($rule);
        }

        return $this->routes[] = new Route ($rule, $cond);
    }
    public function route_get   ($rule = '') {
        return  $this->route($rule, 'GET');
    }
    public function route_post  ($rule = '') {
        return  $this->route($rule, 'POST');
    }
    public function route_match ($uri = NULL, $env = NULL) {

        if   (isset($env)) {
            $this->env             = new Stash ($env);
        }
        if   (isset($uri)) {
            $this->env->uri        = $uri;
            $this->env->is_rewrite = TRUE;
        }
        else {
            $uri                   = $this->env->uri;
        }

        for ($i = count($this->routes); $i--;) {

            $route = array_shift($this->routes);

            if   ($route->name()) {
                $this->routes[$route->name()] = $route;
            }
            else {
                $this->routes[]               = $route;
            }
        }

        $done = 0;

        foreach ($this->routes as $route) {

            $to     = $route->to();
            $args   = $route->test($uri, $this->env->_data());

            if   (! $to || is_null($args)) {
                continue;
            }

            if   (  count($to) == 1 && is_callable($to[0])) {
                $to      = array_shift($to);
            }
            else {

                $target  = array_shift($to);
                $is_file = preg_match ('/[^\\\\\w]/', $target);

                if     ($to) {
                    $method = array_pop($to);
                }
                elseif ($route->name()) {
                    $method = strtr($route->name(), '-', '_');
                }
                else   {
                    $method = 'start';
                }

                if     ($is_file) {
                    require_once $target;
                }

                if     ($to) {
                    $class  = join('\\', $to);
                }
                elseif ($is_file) {
                    $info   = pathinfo($target);
                    $class  = $info['filename'];
                }
                else   {
                    $class  = $target;
                }

                $to      = [new $class ($this), $method];
            }

            foreach ($args as $key => $val) {
                $this->args-> $key =  $val;
            }

            $result = call_user_func($to, $this);

            if   (  $result !== FALSE) {
                $done++;
            }
            if   (  $result !== TRUE) {
                break;
            }
        }

        return $done;
    }

    public function is_valid ($val, $tests) {

        $validator = new Validator ($tests);

        return $validator->test($val);
    }
}

?>
