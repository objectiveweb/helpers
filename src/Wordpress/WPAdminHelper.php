<?php

namespace Objectiveweb\Helpers\Wordpress;

class WPAdminHelper {

    protected $menu;

    protected $title;

    protected $icon = 'admin-generic';

    protected $position = 30;

    public function __construct()
    {
        if(empty($this->title)) {
            $this->title = get_class($this);
        }

        if(!empty($this->menu)) {
            add_action('admin_menu', [$this, 'menu']);
        }

        add_action('admin_init', [$this, 'init']);
    }



    function form($data, $labels = [], $callback) {

        if(!empty($_POST)) {
            call_user_func($callback, $_POST);
            exit;
        }

        if(empty($labels)) {
            foreach(array_keys($data) as $k) {
                $labels[$k] = $k;
            }
        }
        echo "<form action=\"\" method=\"post\"><table class=\"form-table\"><tbody>";

        foreach($labels as $field => $label) {

            if(is_array($label)) {
                $required = !empty($label['required']) ? 'true' : 'false';
                $description = @$label['description'];
                $label = $label['label'];
                $type = empty($label['type'] ? 'text' : $label['type']);

            }
            else {
                $required = 'false';
                $description = '';
                $type = 'text';
            }

            echo "<tr class=\"form-field form-required\">
                <th scope=\"row\"><label for=\"$field\">$label <span class=\"description\">$description</span></label></th>
                <td>";

            echo "<input name=\"$field\" type=\"$type\" id=\"$field\" value=\"{$data[$field]}\" aria-required=\"$required\" autocapitalize=\"none\" autocorrect=\"off\" maxlength=\"60\">";
            echo "</td></tr>";
        }
//	<tr class=\"form-field form-required\">
//		<th scope=\"row\"><label for=\"email\">Email <span class=\"description\">(required)</span></label></th>
//		<td><input name=\"email\" type=\"email\" id=\"email\" value=\"\"></td>
//	</tr>
//	<tr class=\"form-field\">
//		<th scope=\"row\"><label for=\"first_name\">First Name </label></th>
//		<td><input name=\"first_name\" type=\"text\" id=\"first_name\" value=\"\"></td>
//	</tr>
//	<tr class=\"form-field\">
//		<th scope=\"row\"><label for=\"last_name\">Last Name </label></th>
//		<td><input name=\"last_name\" type=\"text\" id=\"last_name\" value=\"\"></td>
//	</tr>
//	<tr class=\"form-field\">
//		<th scope=\"row\"><label for=\"url\">Website</label></th>
//		<td><input name=\"url\" type=\"url\" id=\"url\" class=\"code\" value=\"\"></td>
//	</tr>
//	<tr class=\"form-field form-required user-pass1-wrap\">
//		<th scope=\"row\">
//			<label for=\"pass1-text\">
//				Password				<span class=\"description hide-if-js\">(required)</span>
//			</label>
//		</th>
//		<td>
//			<input class=\"hidden\" value=\" \"><!-- #24364 workaround -->
//			<button type=\"button\" class=\"button wp-generate-pw hide-if-no-js\">Show password</button>
//			<div class=\"wp-pwd hide-if-js\" style=\"display: none;\">
//								<span class=\"password-input-wrapper show-password\">
//					<input type=\"password\" name=\"pass1\" id=\"pass1\" class=\"regular-text strong\" autocomplete=\"off\" data-reveal=\"1\" data-pw=\"xbSNgN^C!)4p(^dCh*Vbw(O&amp;\" aria-describedby=\"pass-strength-result\" disabled=\"\"><input type=\"text\" id=\"pass1-text\" name=\"pass1-text\" autocomplete=\"off\" class=\"regular-text strong\" disabled=\"\">
//				</span>
//				<button type=\"button\" class=\"button wp-hide-pw hide-if-no-js\" data-toggle=\"0\" aria-label=\"Hide password\">
//					<span class=\"dashicons dashicons-hidden\"></span>
//					<span class=\"text\">Hide</span>
//				</button>
//				<button type=\"button\" class=\"button wp-cancel-pw hide-if-no-js\" data-toggle=\"0\" aria-label=\"Cancel password change\">
//					<span class=\"text\">Cancel</span>
//				</button>
//				<div style=\"\" id=\"pass-strength-result\" aria-live=\"polite\" class=\"strong\">Strong</div>
//			</div>
//		</td>
//	</tr>
//	<tr class=\"form-field form-required user-pass2-wrap hide-if-js\" style=\"display: none;\">
//		<th scope=\"row\"><label for=\"pass2\">Repeat Password <span class=\"description\">(required)</span></label></th>
//		<td>
//		<input name=\"pass2\" type=\"password\" id=\"pass2\" autocomplete=\"off\" disabled=\"\">
//		</td>
//	</tr>
//	<tr class=\"pw-weak\" style=\"display: none;\">
//		<th>Confirm Password</th>
//		<td>
//			<label>
//				<input type=\"checkbox\" name=\"pw_weak\" class=\"pw-checkbox\">
//				Confirm use of weak password			</label>
//		</td>
//	</tr>
//	<tr>
//		<th scope=\"row\">Send User Notification</th>
//		<td>
//			<input type=\"checkbox\" name=\"send_user_notification\" id=\"send_user_notification\" value=\"1\" checked=\"checked\">
//			<label for=\"send_user_notification\">Send the new user an email about their account.</label>
//		</td>
//	</tr>
//	<tr class=\"form-field\">
//		<th scope=\"row\"><label for=\"role\">Role</label></th>
//		<td><select name=\"role\" id=\"role\">
//
//	<option value=\"give_worker\">Give Worker</option>
//	<option value=\"give_accountant\">Give Accountant</option>
//	<option value=\"give_manager\">Give Manager</option>
//	<option selected=\"selected\" value=\"subscriber\">Subscriber</option>
//	<option value=\"contributor\">Contributor</option>
//	<option value=\"author\">Author</option>
//	<option value=\"editor\">Editor</option>
//	<option value=\"administrator\">Administrator</option>			</select>
//		</td>
//	</tr>

        echo "</tbody></table>";
        echo "<p class=\"submit\"><input type=\"submit\" class=\"button button-primary\" value=\"Enviar\"></p>";
        echo "</form>";

    }

    function header($title, $desc = null) {
        echo "<h1 id=\"add-new-user\">$title</h1>";
        if($desc) {
            echo "<p>$desc</p>";
        }
    }

    function menu()
    {

        foreach ($this->menu as $slug => $menu) {
            add_menu_page(
                $menu['page_title'], // page_title
                $this->title, // menu_title
                'manage_options', // capability
                $slug, // menu_slug
                array($this, $slug), // function
                'dashicons-'.$this->icon, // icon_url
                $this->position // position
            );

            if (!empty($menu['children'])) {
                add_submenu_page($slug, //parent slug
                    $menu['page_title'], // page_title
                    $menu['title'], // menu_title
                    'manage_options', // capability
                    $slug // menu_slug
                );

                foreach ($menu['children'] as $ck => $child) {
                    add_submenu_page($slug, //parent slug
                        $child['page_title'], // page_title
                        $child['title'], // menu_title
                        'manage_options', // capability
                        $ck, // menu_slug
                        [$this, $ck]
                    );
                }
            }
        }
    }

    function redirect($to) {
        $location = '/wordpress/wp-admin/admin.php?page='.$to;

        echo "<script> window.location.href= '$location'</script>";
        exit;
    }

    function table($data, $labels = [], $actions = []) {

        if(empty($data['data'])) {
            return;
        }

        if(empty($labels)) {
            foreach(array_keys($data['data'][0]) as $key) {
                $labels[$key] = $key;
            }
        }
        echo "<table class=\"wp-list-table widefat fixed striped posts\">";
        echo "<thead><tr>
                <td id=\"cb\" class=\"manage-column column-cb check-column\">
                <label class=\"screen-reader-text\" for=\"cb-select-all-1\">Select All</label>
                <input id=\"cb-select-all-1\" type=\"checkbox\">
                </td>";

        foreach($labels as $col => $title) {
            echo "<th scope=\"col\" id=\"$col\" class=\"manage-column column-title column-primary sortable desc\">
                <a href=\"?orderby=$col&amp;order=asc\">";
            printf("<span>%s</span>", is_callable($title) ? $title(null) : $title);
            echo "<span class=\"sorting-indicator\"></span></a></th>";
        }
        ////<th scope="col" id="author" class="manage-column column-author">Author</th><th scope="col" id="categories" class="manage-column column-categories">Categories</th><th scope="col" id="tags" class="manage-column column-tags">Tags</th><th scope="col" id="comments" class="manage-column column-comments num sortable desc"><a href="http://goodsports.localhost/wordpress/wp-admin/edit.php?orderby=comment_count&amp;order=asc"><span><span class="vers comment-grey-bubble" title="Comments"><span class="screen-reader-text">Comments</span></span></span><span class="sorting-indicator"></span></a></th><th scope="col" id="date" class="manage-column column-date sortable asc"><a href="http://goodsports.localhost/wordpress/wp-admin/edit.php?orderby=date&amp;order=desc"><span>Date</span><span class="sorting-indicator"></span></a></th>	</tr>//
        if(!empty($actions)) {
            echo "<th></th>";
        }
        echo "</tr></thead>";

        echo "<tbody id=\"the-list\">";
        foreach($data['data'] as $line) {

            echo "<tr id=\"gs_user-{$line['id']}\" class=\"\">";
            echo "<th scope=\"row\" class=\"check-column\">	
			<input id=\"cb-select-{$line['id']}\" type=\"checkbox\" name=\"gs_user[]\" value=\"{$line['id']}\">
			</th>";

            foreach($labels as $k => $v) {
                printf("<td data-colname=\"%s\">
<strong>%s</strong>
</td>", $k, is_callable($v)? call_user_func($v, $line) : $line[$k]);

                //	echo "<span aria-hidden=\"true\">—</span><span class=\"screen-reader-text\">No comments</span><span class=\"post-com-count post-com-count-pending post-com-count-no-pending\"><span class=\"comment-count comment-count-no-pending\" aria-hidden=\"true\">0</span><span class=\"screen-reader-text\">No comments</span></span>		</div>
                //	</td><td class=\"date column-date\" data-colname=\"Date\">Published<br><abbr title=\"2017/08/25 12:01:50 pm\">2017/08/25</abbr></td>
                //	</tr>";
            }
            //<div class="locked-indicator">
//				<span class="locked-indicator-icon" aria-hidden="true"></span>
//		    	<span class="screen-reader-text">“Colombiano Miguel Lopez ataca de novo e vence em Sierra Nevada” is locked</span>
//			</div>

            if(!empty($actions)) {
                echo "<td>";
                foreach($actions as $action_name => $action) {
                    printf("<a href=\"%s\">%s</a>", str_replace("%", $line['id'], $action), $action_name);
                }
                echo "</td>";
            }
//            <div class="row-actions"><span class="edit"><a href="http://goodsports.localhost/wordpress/wp-admin/post.php?post=1037&amp;action=edit" aria-label="Edit “Colombiano Miguel Lopez ataca de novo e vence em Sierra Nevada”">Edit</a> | </span><span class="inline hide-if-no-js">
//                <a href="#" class="editinline" aria-label="Quick edit “Colombiano Miguel Lopez ataca de novo e vence em Sierra Nevada” inline">Quick&nbsp;Edit</a> | </span><span class="trash">
//                <a href="http://goodsports.localhost/wordpress/wp-admin/post.php?post=1037&amp;action=trash&amp;_wpnonce=c333c9d19a" class="submitdelete" aria-label="Move “Colombiano Miguel Lopez ataca de novo e vence em Sierra Nevada” to the Trash">Trash</a> |
//                </span><span class="view"><a href="http://goodsports.localhost/2017/09/03/colombiano-miguel-lopez-ataca-de-novo-e-vence-em-sierra-nevada/" rel="bookmark" aria-label="View “Colombiano Miguel Lopez ataca de novo e vence em Sierra Nevada”">View</a></span></div>
        }

        echo "</tbody>";
    }


    function wrap($callback, $param = null) {

        echo "<div class=\"wrap\">";

        try {
            call_user_func($callback, $param);
        }
        catch(\Exception $ex) {
            echo $ex->getMessage();
        }
        echo "</div>";
    }

    function init() {}
}