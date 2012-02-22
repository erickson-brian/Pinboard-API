<?php

class PinboardPost {

	private $user;			// pinboard user
	private $pass;			// pinboard pass
	private $url;			// api url
	private $debug = true;	// debug spits our url being called
	private $http_code;		// last http status code

	private $properties = array(
			"url"	=>	"",
			"time"	=>	"",
			"description"	=>	"",
			"extended"		=>	"",
			"tag"			=>	array(),
			"hash"			=>	"",
			"meta"			=>	"",
			"shared"		=>	"",
			"toread"		=>	""
	);

	// construct
	public function pinboardPost($user, $pass, $data = array()) {
		foreach($data AS $key=>$val) {
			if($key == "tag") {
				$val = explode(" ", $val);
			}
			$this->properties[$key] = $val;
		}

		$this->user = $user;
		$this->pass = $pass;
		$this->url = "https://" . $user . ":" . $pass . "@api.pinboard.in/v1/";
	}

	// saves this post
	// returns api response
	public function addPost() {
		if(empty($this->properties["url"]) || empty($this->properties["description"])) {
			throw new exception("Missing url or description, both are required");
		} else {
			$method = "posts/add";

			foreach($this->properties AS $key=>$val) {
				if($key == "tag") {
					if(count($val)>0)
						$args[] = "tag=" . implode("+", array_map('urlencode', $val));
				} elseif(!empty($val)) {
					$args[] = $key ."=". urlencode($val);
				}
			}	

			return $this->xmlToArray($this->call($method, $args));
		}
	}

	// delete this current post
	// returns api response
	public function deletePost() {
		if (empty($this->properties["url"])) {
			throw new exception("Need to url to delete");
		} else {
			$method = "posts/delete";
			$args[] = "url=" . urlencode($this->properties["url"]);

			return $this->xmlToArray($this->call($method, $args));
		}
	}

	// suggest tags for your url
	public function suggestTags() {
		if (empty($this->properties["url"])) {
			throw new exception("Need to url to suggest tags");
		} else {
			$method = "posts/suggest";
			$args[] = "url=" . urlencode($this->properties["url"]);

			$tags = $this->xmlToArray($this->call($method, $args));

			return $tags["recommended"];
		}	
	}

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
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
		$buffer = curl_exec($ch);
		$this->http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close ($ch);			
		
		return $buffer;
	}	

	// takes in xml response, returns array
	private function xmlToArray($xml) {
		// convert xml to array (prob should just get json...)
		$xml = simplexml_load_string($xml);
		$json = json_encode($xml);
		return json_decode($json,TRUE);
	}	

	// debugging function
	public function getLastHttpCode() {
		return $this->http_code;
	}
}