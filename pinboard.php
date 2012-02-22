<?php

require_once("pinboard_post.php");

class Pinboard {

	private $user = "";			//pinboard username
	private $pass = "";			//pinboard password
	private $url = "";			//pinboard api location
	private $debug = false;		//debug on/off
	private $http_code = "";	//status code of last request
	
	// construct
	public function Pinboard($user, $pass) {
		$this->user = $user;
		$this->pass = $pass;
		$this->url = "https://" . $user . ":" . $pass . "@api.pinboard.in/v1/";
	}
	

// Posts Methods //
	
	// takes in filters on tags, start spot, limit, and dates
	// return array of all posts objects
	public function getAllPosts($tags = array(), $start = "", $results = "", $fromDate = "", $toDate = "") {
		$method = "posts/all";

		// tags
		if(count($tags)>0) {
			$args[] = "tag=" . implode("+", array_map('urlencode', $tags));
		}

		// start
		if(!empty($start)) {
			$args[] = "start=" . $start;
		}

		// results
		if(!empty($results)) {
			$args[] = "results=" . $results;	
		}

		// fromDate
		if(!empty($fromDate)) {
			$args[] = "fromdt=" . urlencode($fromDate);
		}
		
		// toDate
		if(!empty($fromDate)) {
			$args[] = "todt=" . urlencode($toDate);
		}

		$response = $this->call($method, $args);

		return $this->postsResponseToArray($response);
	}

	// get post from specific date with tag & url filters
	// return array of matching post objects
	public function getPosts($tags = array(), $date = "", $url = "") {
		$method = "posts/get";
		
		// tags
		if(count($tags)>0) {
			$args[] = "tag=" . implode("+", array_map('urlencode', $tags));
		}

		// Date
		if(!empty($date)) {
			$args[] = "dt=" . urlencode($date);
		}

		// url
		if(!empty($url)) {
			$args[] = "url=" . urlencode($url);	
		}

		$response = $this->call($method, $args);

		return $this->postsResponseToArray($response);
	}

	public function getRecentPosts($tags = array(), $count = "") {
		$method = "posts/recent";

		// tags
		if(count($tags)>0) {
			$args[] = "tag=" . implode("+", array_map('urlencode', $tags));
		}

		// count
		if(!empty($count)) {
			$args[] = "count=" . urlencode($count);
		}

		$response = $this->call($method, $args);
		return $this->postsResponseToArray($response);
	}

	// takes array of tags (up to 3)
	// returns assoc array of post dates with counts
	public function getDates($tags = array()) {
		$method = "posts/dates";

		// tags
		if(count($tags)>0) {
			$args[] = "tag=" . implode("+", array_map('urlencode', $tags));
		}
		
		$dates = $this->xmlToArray($this->call($method, $args));
		
		foreach($dates["date"] AS $dd) {
			$r_dates[$dd["@attributes"]["date"]]["count"] = $dd["@attributes"]["count"];
		}

		return $r_dates;

	}

	// returns datetime of last bookmark modification
	public function lastUpdate() {
		$method = "posts/update";

		$response = $this->xmlToArray($this->call($method));
		return $response["@attributes"]["time"];
	}

	// takes in xml response from api
	// returns array of PinboardPost objects
	// @todo catch empties
	private function postsResponseToArray($response) {
		$posts = $this->xmlToArray($response);
		
		foreach($posts["post"] AS $pp) {
			$r_posts[] = new PinboardPost($this->user, $this->pass, $pp);
		}

		return $r_posts;
	}


// Ends Posts //


// Tags Methods //

	// return assoc array of all tags with counts
	public function getTags() {
		$method = "tags/get";
	
		$tags = $this->xmlToArray($this->call($method));

		foreach($tags["tag"] as $tag)
		{
			$r_tags[$tag["@attributes"]["tag"]]["count"] = $tag["@attributes"]["count"];
		}
		return $r_tags;
	}

	// renames tag from old to new
	// returns api response
	public function renameTag($old, $new) {
		$method = "tags/rename";

		$args[] = "old=" . urlencode($old);
		$args[] = "new=" . urlencode($new);

		$response = $this->call($method, $args);
		
		return $response;
	}

	// deletes tag
	// returns api response
	public function deleteTag($tag) {
		$method = "tags/delete";

		$args[] = "tag=" . urlencode($tag);

		$response = $this->call($method, $args);

		return $response;
	}

// End Tags //


// User Methods //

	public function getSecret() {
		$method = "user/secret";

		return $this->call($method);
	}

// End User //	

// Helper Methods (private) //

	// calls api
	// return api response
	private function call($method, $args = array()) {
		$timeout = 30;

		// glue all args together
		if(count($args)) {
			$arg = implode("&", $args);
		}

		// add args to method
		if(strlen($arg)>0) {
			$method .= "?" . $arg;
		}

		$url = $this->url . $method;

		if($this->debug)
			echo($url ."<br>");
		
		//exit();
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$buffer = curl_exec($ch);
		$this->http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close ($ch);			
		
		return $buffer;
	}	

	// takes in xml response, returns array
	private function xmlToArray($xml)
	{
		// convert xml to array (prob should just get json...)
		$xml = simplexml_load_string($xml);
		$json = json_encode($xml);
		return json_decode($json,TRUE);
	}

	// help with debugging
	public function getLastHttpCode() {
		return $this->http_code;
	}

// End Helpers //
	
}


