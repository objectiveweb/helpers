<?php

namespace Objectiveweb\Helper\Wordpress;

class WPPluginHelper {

    protected $pages = [];

    protected $template_root = null;

    public function __construct($template_root = null)
    {
        $this->template_root = $template_root;

        if(!empty($this->pages)) {

            add_action('parse_request', [$this, 'parse_request']);

        }

        add_action('init', [$this, 'init']);
    }

    public function init() {

    }

    function render($page, $data = []) {
        $_template = $this->template_root."/$page.php";

        if(!is_readable($_template)) {
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

    function parse_request(&$wp) {
        if (empty($wp->query_vars['pagename']))
            return; // page isn't permalink

        $pagename = $wp->query_vars['pagename'];

        if(!in_array($pagename, array_keys($this->pages)))
            return;

        $page = $this->pages[$pagename];

        if(!empty($_POST)) {

            if(empty($page['callback'])) {
                $callback = [$this, 'post'.ucfirst($pagename)];
            }
            else {
                $callback = $page['callback'];
            }

            if(is_string($callback)) {
                $callback = [$this, $callback];
            }

            $page['content'] = call_user_func($callback, $_POST);

        }

        $page['name'] = $pagename;

        if(empty($page['content'])) {
            $page['content'] = is_callable([$this, $pagename]) ?
                call_user_func([$this, $pagename], $_GET)
                : $this->render($pagename);
        }
        remove_all_filters('the_content');
        add_filter('the_posts', function() use($page){
            return $this->createdummypage($page['name'], $page['title'], $page['content']);
        });
    }

    function redirect($to) {
        $location = '/'.$to;

        echo "<script> window.location.href= '$location'</script>";
        exit;
    }

    // Setup a dummy page
    //
    function createdummypage($slug, $title, $body)
    {
        // have to create a dummy post as otherwise many templates
        // don't call the_content filter
        global $wp, $wp_query;
        //create a fake post intance
        $p = new \stdClass;
        // fill $p with everything a page in the database would have
        $p->ID = -1;
        $p->post_author = 1;
        $p->post_date = current_time('mysql');
        $p->post_date_gmt =  current_time('mysql', $gmt = 1);
        $p->post_content = $body;
        $p->post_title = $title;
        $p->post_excerpt = '';
        $p->post_status = 'publish';
        $p->ping_status = 'closed';
        $p->post_password = '';
        $p->post_name = $slug; // slug
        $p->to_ping = '';
        $p->pinged = '';
        $p->modified = $p->post_date;
        $p->modified_gmt = $p->post_date_gmt;
        $p->post_content_filtered = '';
        $p->post_parent = 0;
        $p->guid = get_home_url('/' . $p->post_name); // use url instead?
        $p->menu_order = 0;
        $p->post_type = 'page';
        $p->post_mime_type = '';
        $p->comment_status = 'closed';
        $p->comment_count = 0;
        $p->filter = 'raw';
        $p->ancestors = array(); // 3.6
        // reset wp_query properties to simulate a found page
        $wp_query->is_page = TRUE;
        $wp_query->is_singular = TRUE;
        $wp_query->is_home = FALSE;
        $wp_query->is_archive = FALSE;
        $wp_query->is_category = FALSE;
        unset($wp_query->query['error']);
        $wp->query = array();
        $wp_query->query_vars['error'] = '';
        $wp_query->is_404 = FALSE;
        $wp_query->current_post = $p->ID;
        $wp_query->found_posts = 1;
        $wp_query->post_count = 1;
        $wp_query->comment_count = 0;
        // -1 for current_comment displays comment if not logged in!
        $wp_query->current_comment = null;
        $wp_query->is_singular = 1;
        $wp_query->post = $p;
        $wp_query->posts = array($p);
        $wp_query->queried_object = $p;
        $wp_query->queried_object_id = $p->ID;
        $wp_query->current_post = $p->ID;
        $wp_query->post_count = 1;
        return array($p);
    }
}