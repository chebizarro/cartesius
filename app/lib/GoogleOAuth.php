<?php

namespace Slim\Extras\Middleware;

//require MODULES.'cartesius/auth/model.php';

use \Google_Client;
use \Google_PlusService;
use \Google_Oauth2Service;

class GoogleOAuth extends \Slim\Middleware
{

	private $client;
	private $plus;
	private $oauth2;
	private $params;

	public function __construct($params)
	{
		$this->params = $params;
		$this->client = new Google_Client();
		$this->client->setApplicationName($params['name']);
		$this->client->setClientId($params['client_id']);
		$this->client->setClientSecret($params['client_secret']);
		$this->client->setRedirectUri($params['redirect_uri']);
		$this->client->setDeveloperKey($params['developer_key']);

		$this->plus = new Google_PlusService($this->client);
		$this->oauth2 = new Google_Oauth2Service($this->client);

	}

	public function call()
	{
		$req = $this->app->request();
		$app = $this->app;
        $config = $this->params;
        $domain = '';
        
        $this->app->hook('slim.before.router', function () use ($app, $req, $config) {
            $secured_urls = isset($config['security.urls']) && is_array($config['security.urls']) ? $config['security.urls'] : array();
            foreach ($secured_urls as $surl) {
                $patternAsRegex = $surl['path'];
                if (substr($surl['path'], -1) === '/') {
                    $patternAsRegex = $patternAsRegex . '?';
                }
                $patternAsRegex = '@^' . $patternAsRegex . '$@';

                if (preg_match($patternAsRegex, $req->getPathInfo())) {
                    if (!isset($_SESSION['access_token'])) {
					
						if($req->get('code')) {
							$this->client->authenticate($req->get('code'));
							$_SESSION['access_token'] = $this->client->getAccessToken();
							$this->add_user($this->client->getAccessToken());
							$this->get_user_id();
                            $app->redirect($req->getPathInfo());
						} else {
					
                        //if ($req->getPath() !== $config['login.url']) {
							
							if(isset($config['domain']))
								$domain = urlencode('&hd='.$config['domain']);
							
                            $app->redirect($config['login.url'].'?redirect='.urlencode($this->client->createAuthUrl()).$domain);
                        }
					}
                
                }
            }
        });

		$this->next->call();

	}
	
	function add_user($token) {
		$user = $this->oauth2->userinfo->get();
		$email = filter_var($user['email'], FILTER_SANITIZE_EMAIL);

		$me = $this->plus->people->get('me');
		$img = strtok(filter_var($me['image']['url'], FILTER_VALIDATE_URL), '?');
		$name = filter_var($me['displayName'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);

		//$newuser = XMLModel::factory('Account')->find_one($id);
		if(\Account::where('email', $email)->count() == 0) {
			$user = \Account::create();
			$user->email = $email;
			$user->username = $name;
			$user->image = $img;
			$user->token = $token;
			$user->role = 0;
			$user->save();
		}
		
	}

	function get_user_id(){
		$user = $this->oauth2->userinfo->get();
		$email = filter_var($user['email'], FILTER_SANITIZE_EMAIL);
		$user = \Account::where('email', $email)->find_one();
		$_SESSION['userid'] = $user->id;
	}
}
