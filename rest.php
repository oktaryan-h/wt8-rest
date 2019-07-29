<?php

class REST_API {

	public function show_php_info() {
		phpinfo();
	}
	public function get_json_data( $url ) {
		return file_get_contents( $url );;
	}
}

class Wordpress {

	/**
	 * [_post_entries_default_args description]
	 * @param  array  $args [description]
	 * @return [type]       [description]
	 */
	function _post_entries_default_args( $args = array() ) {
		$default_args = array(
			'title',
			'content',
			'date'
		);
		if ( is_array( $args ) ) {
			if ( empty ( $args ) ) {
				$args = $default_args;
			}
			else {
				$args = array_intersect( $args, $default_args );
			}
		} else {
			$args = array( $args );
		}
		return $args;
	}

	/**
	 * [decode_from_json description]
	 * @param  [type] $source [description]
	 * @return [type]         [description]
	 */
	public function decode_from_json( $source ) {

		return json_decode( $source );

	}

	/**
	 * [post_entries description]
	 * @param  [type] $json [description]
	 * @param  array  $args [description]
	 * @return [type]       [description]
	 */
	public function post_entries( $json, $args = array() ) {

		$args = $this->_post_entries_default_args( $args );

		$json_decoded = $this->decode_from_json( $json );

		$post_entries = array();

		foreach ( $json_decoded as $a ) {
			$x = array();
			if ( in_array( 'title', $args ) ) {
				$x['title'] = $a->title->rendered;
			}
			if ( in_array( 'content', $args ) ) {
				$x['content'] = $a->content->rendered;
			}
			if ( in_array( 'date', $args ) ) {
				$x['date'] = $a->date;
			}
			$post_entries[] = $x; 
		}

		return $post_entries;

	}

	/**
	 * [html description]
	 * @param  [type]  $json [description]
	 * @param  array   $args [description]
	 * @param  boolean $echo [description]
	 * @return [type]        [description]
	 */
	public function html( $json, $args = array(), $echo = true ) {

		$args = $this->_post_entries_default_args( $args );

		$post_entries = $this->post_entries( $json );

		if ( false == $echo ) {
			ob_start();
		}

		foreach ( $post_entries as $a ) {
			echo '<div>';
			if ( in_array( 'title', $args ) ) {
				echo '<p style="font-weight:600">' . $a['title'] . '</p>';
			}
			if ( in_array( 'content', $args ) ) {
				echo '<p>' .  $a['content']  . '</p>';
			}
			if ( in_array( 'date', $args ) ) {
				echo '<p>' . $a['date'] . '</p>';
			}
			echo '</div>';
		}

		if ( false == $echo ) {
			return ob_get_clean();
		}
	}
}

/**
 * [show_posts description]
 * @param  [type] $page  [description]
 * @param  [type] $limit [description]
 * @param  array  $args  [description]
 * @return [type]        [description]
 */
function show_posts( $page, $limit, $args = array() ) {

	$url = "http://localhost/wordpress/wp-json/wp/v2/posts/?page=$page&per_page=$limit";

	$rest = new REST_API;
	$wp = new Wordpress;

	$json_data = $rest->get_json_data( $url );
	$wp->html( $json_data, $args, true );

}

// TEST CALLS

show_posts( 1, 2, array( 'title', 'date' ) );



$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, "http://localhost/wordpress/wp-json/wp/v2/posts/?page=$page&per_page=$limit");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

$co = curl_exec($ch);

//echo $co;

$username = 'user';
$password = '1234';
// the standard end point for posts in an initialised Curl
$process = curl_init('http://localhost/wordpress/wp-json/wp/v2/posts');
// create an array of data to use, this is basic - see other examples for more complex inserts
$data = array('slug' => 'rest_insert_test' , 'title' => 'REST API insert' , 'content' => 'The content of our stuff', 'excerpt' => 'smaller' );
$data_string = json_encode($data);
// create the options starting with basic authentication
curl_setopt($process, CURLOPT_USERPWD, $username . ":" . $password);
curl_setopt($process, CURLOPT_TIMEOUT, 30);
curl_setopt($process, CURLOPT_POST, 1);
// make sure we are POSTing
curl_setopt($process, CURLOPT_CUSTOMREQUEST, "POST");
// this is the data to insert to create the post
curl_setopt($process, CURLOPT_POSTFIELDS, $data_string);
// allow us to use the returned data from the request
curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
// we are sending json
curl_setopt($process, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Content-Length: ' . strlen($data_string))
);
// process the request
$return = curl_exec($process);
curl_close($process);
// This buit is to show you on the screen what the data looks like returned and then decoded for PHP use
echo '<h2>Results</h2>';
print_r($return);
echo '<h2>Decoded</h2>';
$result = json_decode($return, true);
print_r($result);