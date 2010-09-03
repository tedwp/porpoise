<?php

class TestWebApp extends WebApp {
	
	public function test() {
		$view = array(
			'title' => 'Test App',
			'content' => 'Action <em>test</em> called on Test layer'
		);
		return $view;
	}
	
	public function two_legged() {
		$res = $this->http->get('http://oauth-sandbox.sevengoslings.net/two_legged');
		
		$view = array(
			'title' => 'Test Two Legged',
			'content' => sprintf('Result: %s<br>Code: %s<br>Headers: <pre>%s</pre>', 
				$this->http->getResponseData(), 
				$this->http->getResponseCode(), 
				print_r($this->http->getResponseHeaders(), true))
		);
		return $view;
	}
	
	public function three_legged() {
		$res = $this->http->get('http://oauth-sandbox.sevengoslings.net/three_legged');
		
		$view = array(
			'title' => 'Test Three Legged',
			'content' => sprintf('Result: %s<br>Code: %s<br>Headers: <pre>%s</pre>', 
				$this->http->getResponseData(), 
				$this->http->getResponseCode(), 
				print_r($this->http->getResponseHeaders(), true))
		);
		return $view;
	}
	
	/**
	 * Override the persistUser method to add additional user data
	 * finally call parent method to add basic user data and persist the user object.
	 * @return void
	 * @param object $token
	 */
	protected function persistUser($token) {
		$this->user->setApp_user_name('test');
		$this->user->setApp_uid(1234);
		parent::persistUser($token);
	}
	
	/**
	 * Example: overrride render method to add content
	 * @return 
	 * @param object $view
	 */
	protected function render(array $view) {
		$user = $this->user->getApp_user_name();
		// hack: user is logged in 
		if ($user) {
			$xtra = sprintf('<ul><li><a href="%s">Layer action "test"</a></li>', $this->getActionUrl('test'));
			$xtra .= sprintf('<li><a href="%s">Test Two legged OAuth call</a></li>', $this->getActionUrl('two_legged'));
			$xtra .= sprintf('<li><a href="%s">Test Three legged OAuth call</a></li></ul>', $this->getActionUrl('three_legged'));
			$view['content'] = $xtra . $view['content'];
		}
		parent::render($view);
	}
	
}


?>