<?php

namespace Objectiveweb\Helper\Wordpress;

use Objectiveweb\Router;

abstract class PluginHelper
{

    private $app;

    /**
     * A page can be
     *  - a controller classname
     *  - an array with [ 'title', 'content' ]
     *  - a callback
     * @var array
     */
    protected $pages = [];

    protected $template_root = null;

    public function __construct(Router $app, $template_root = null)
    {
        $this->app = $app;


        $this->template_root = $template_root;

        if (!empty($this->pages)) {

            add_action('parse_request', [$this, 'parse_request']);

        }

        add_action('init', [$this, 'init']);

    }

    protected function init()
    {

    }

    function parse_request(&$wp)
    {
        if (empty($wp->query_vars['pagename']))
            return; // page isn't permalink

        // get pagename from url
        $pagename = explode("/", $wp->query_vars['pagename']);
        $pagename = $pagename[0];

        if (!in_array($pagename, array_keys($this->pages)))
            return;

        if (substr($_SERVER['REQUEST_URI'], -1) == "/") {
            $_SERVER['PATH_INFO'] = substr($_SERVER['REQUEST_URI'], 0, -1);
        } else {
            $_SERVER['PATH_INFO'] = $_SERVER['REQUEST_URI'];
        }

        $controllerName = $this->pages[$pagename];

        // prevent redirects from /pagename/1 to /pagename
        remove_action('template_redirect', 'redirect_canonical');

        // Add default serializer for custom Wordpress\Page
        Router::addSerializer(Page::class, function (Page $page_object) use ($pagename) {

            // render page if it has vars defined
            if (is_array($page_object->vars)) {
                $page_object->content = $this->render($page_object->content, $page_object->vars);
            }

            // create fake posts list
            $the_posts = $page_object->create($pagename);

            // override wp_document_title
            add_filter('pre_get_document_title', function() use ($page_object) {
                return $page_object->title;
            });

            // don't filter contents on fake pages
            remove_all_filters('the_content');

            // return fake list of posts
            add_filter('the_posts', function () use ($the_posts) {
                return $the_posts;
            });

        });

        if (is_callable($controllerName)) {
            return call_user_func($controllerName, $pagename);
        } elseif(is_string($controllerName) && class_exists($controllerName)) {
            $this->app->controller("/$pagename", $controllerName);
        } else {
            // TODO some default for arrays/strings
        }
    }

    function redirect($to)
    {
        $location = '/' . $to;

        echo "<script> window.location.href= '$location'</script>";
        exit;
    }

    function render($page, $data = [])
    {
        $_template = $this->template_root . "/$page.php";

        return Router::render($_template, $data);
    }

}
