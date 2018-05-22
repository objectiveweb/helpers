<?php

namespace Objectiveweb\Helper\Wordpress;

use Objectiveweb\Router;

abstract class WPPluginHelper
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

        Router::addSerializer(\Exception::class, function(\Exception $ex) {
            global $wp, $wp_query;

            $page = new Page('Error');


            $page->content = $this->render('error', ['message' => $ex->getMessage(), 'code' => $ex->getCode() ]);
            $the_posts = $page->create($wp->query_vars['pagename']);

            if($ex->getCode() == 404) {
                $wp_query->set_404();
            }
            status_header($ex->getCode());

            nocache_headers();

            remove_all_filters('the_content');
            add_filter('the_posts', function () use ($the_posts) {
                return $the_posts;
            });
        });

    }

    protected abstract function init();


    function parse_request(&$wp)
    {
        if (empty($wp->query_vars['pagename']))
            return; // page isn't permalink

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

        Router::addSerializer(Page::class, function (Page $page) use ($pagename) {
            if (!empty($page->vars)) {
                $page->content = $this->render($page->content, $page->vars);
            }

            $the_posts = $page->create($pagename);
            remove_all_filters('the_content');
            add_filter('the_posts', function () use ($the_posts) {
                return $the_posts;
            });
        });

        try {
            $this->app->controller("/$pagename", $controllerName);
        } catch (\Exception $ex)  {
            header(sprintf('HTTP/1.1 %s', $ex->getCode()));

            $page = new Page("Error", $ex->getMessage());
            $page->content = $this->render('error', ['message' => $ex->getMessage(), 'code' => $ex->getCode()]);

            $the_posts = $page->create($pagename);
            remove_all_filters('the_content');
            add_filter('the_posts', function () use ($the_posts) {
                return $the_posts;
            });
exit("ex");
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

        if (!is_readable($_template)) {
            error_log("Cannot read $_template");
            return "";
        }

        extract($data);

        ob_start();
        include $_template;
        $contents = ob_get_contents();
        ob_end_clean();

        return $contents;
    }

}