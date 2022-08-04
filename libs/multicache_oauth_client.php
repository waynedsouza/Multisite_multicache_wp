<?php

/**

 * MulticacheWP

 * uri: http://onlinemarketingconsultants.in
 Author URI: http://onlinemarketingconsultants.in
 * Description: High Performance fastcache Controller

 * Version: 1.0.0.6

 * Author: Wayne DSouza

 * Author URI: http://multicache.org

 * License: GNU PUBLIC LICENSE see license.txt

 */

defined('_MULTICACHEWP_EXEC') or die();



class MulticacheOauthClient 

{

	protected $options;

	//public static $_cacheid = NULL;



    public function __construct($options = null/*, JHttp $http = null, JInput $input = null, JApplicationWeb $application = null*/)

    {

    	$this->options = isset($options) ? $options : new stdClass(); //new Registry;

    	$this->input = $_REQUEST["code"] ;

        //parent::__construct($options, $http, $input, $application);

    

    }



   

    public function authenticate()

    {

    	$debug = true;



        if ($data['code'] = $this->input)

        {

            $data['grant_type'] = 'authorization_code';

            $data['redirect_uri'] = $this->getOption('redirecturi');

            $data['client_id'] = $this->getOption('clientid');

            $data['client_secret'] = $this->getOption('clientsecret');

            $args = array('body' => $data);

            //$response = $this->http->post($this->getOption('tokenurl'), $data);

            $response_array = wp_remote_post($this->getOption('tokenurl'),$args);

            $response = $this->toObject($response_array);

            if($debug)

            {

            	MulticacheHelper::log_error('OAuth Authenticate response obj','oauth_authentication_response',$response);

            }

            if ($response->response->code >= 200 && $response->response->code < 400)

            {

                if ($response->headers->content-type == 'application/json' || $response->headers->content-type == 'application/json; charset=utf-8')

                {

                    $token = array_merge(json_decode($response->body, true), array(

                        'created' => time()

                    ));

                }

                else

                {

                    parse_str($response->body, $token);

                    $token = array_merge($token, array(

                        'created' => time()

                    ));

                }

                $this->setToken($token);

                return $token;

            }

            else

            {

                throw new RuntimeException('Error code ' . $response->response->code . ' received requesting access token: ' . $response->response->message . '.',$response->response->code);

            }

        }

        if ($this->getOption('sendheaders'))

        {

        	$create_url = $this->createUrl();

        	if($debug)

        	{

        		MulticacheHelper::log_error('create url new code','oauth_authentication_response',$create_url);

        	}

        	wp_redirect($create_url);

        	exit;

           //use WP redirect here

			//$this->application->redirect($this->createUrl());

        }

        return false;

    

    }



    /**

     * Send a signed Oauth request.

     *

     * @param string $url

     *        The URL forf the request.

     * @param mixed $data

     *        The data to include in the request

     * @param array $headers

     *        The headers to send with the request

     * @param string $method

     *        The method with which to send the request

     * @param int $timeout

     *        The timeout for the request

     *        

     * @return string The URL.

     *        

     * @since 12.3

     * @throws InvalidArgumentException

     * @throws RuntimeException

     */

    public function query($url, $data = null, $headers = array(), $method = 'get', $timeout = null)

    {

$debug = true;

        $token = $this->getToken();

        if($debug)

        {

        	MulticacheHelper::log_error('Does token have the expires and created array','oauth_client',$token);

        }

        if (array_key_exists('expires_in', $token) && $token['created'] + $token['expires_in'] < time() + 20)

        {

            if (! $this->getOption('userefresh'))

            {

                return false;

            }

            $token = $this->refreshToken($token['refresh_token']);

        }

        if (! $this->getOption('authmethod') || $this->getOption('authmethod') == 'bearer')

        {

            $headers['Authorization'] = 'Bearer ' . $token['access_token'];

        }

        elseif ($this->getOption('authmethod') == 'get')

        {

            if (strpos($url, '?'))

            {

                $url .= '&';

            }

            else

            {

                $url .= '?';

            }

            $url .= $this->getOption('getparam') ? $this->getOption('getparam') : 'access_token';

            // $url .= '=' . $token['access_token'];

        }

        

        $args = array('headers' => $headers,'body' => $data,'data' => $data);

        

        if(!empty($timeout))

        {

        	$args['timeout'] = $timeout;

        }

        if($debug)

        {

        	MulticacheHelper::log_error('Pre Query Method ouath args array','oauth_client',$args);

        	MulticacheHelper::log_error('Pre Query Method ouath url','oauth_client',$url);

        }

        switch ($method)

        {

            case 'head':

            	$response_array = wp_remote_head($url, $args);

            	break;

            case 'get':

            	$args = array('headers' => $headers);

            	$response_array = wp_remote_get($url, $args);

            	if($debug)

            	{

            	MulticacheHelper::log_error('Query Method ouath response array','oauth_client',$response_array);	

            	}

            	break;

            case 'delete':

            	$args["method"] = 'DELETE';

            	$response_array = wp_remote_request($url , $args);

            	break;

            case 'trace':

            	$args["method"] = 'TRACE';

            	$response_array = wp_remote_request($url , $args);

                //$response = $this->http->$method($url, $headers, $timeout);

                break;                

            case 'post':

            	$args["body"] = $data;

            	$response_array = wp_remote_post($url , $args);

            	break;            	

            case 'put':

            	$args["body"] = $data;

            	$args["method"] = 'PUT';

            	$response_array = wp_remote_request($url , $args);

            case 'patch':

            	$args["body"] = $data;

            	$args["method"] = 'PATCH';

            	$response_array = wp_remote_request($url , $args);

               // $response = $this->http->$method($url, $data, $headers, $timeout);

                break;

            default:

                throw new InvalidArgumentException('Unknown HTTP request method: ' . $method . '.');

        }

        	

        $response = $this->toObject($response_array);

     if($debug)

        {

        	MulticacheHelper::log_error('End Query Method ouath response in object','oauth_client',$response );

        	MulticacheHelper::log_error('End Query Method ouath response ->reponse','oauth_client',$response->response );

        	MulticacheHelper::log_error('End Query Method ouath response ->reponse- >code','oauth_client',$response->response->code );

        	MulticacheHelper::log_error('End Query Method ouath response ->reponse- >code','oauth_client',$response->response->code );

        	

        }

        

        if ($response->response->code < 200 || $response->response->code >= 400)

        {

        	$e_message = 'Error code ' . $response->response->code . ' received requesting data Message from Google : ' . $response->response->message . '.';

            throw new RuntimeException($e_message , $response->response->code);

        }

        

        return $response;

    

    }

    public function getOption($key)

    {

    	return $this->options->$key;//get($key);

    }

    

    public function setOption($key, $value)

    {

    	$this->options->$key = $value;//set($key, $value);

    	return $this;

    }

    

    public function getToken()

    {

    	return $this->getOption('accesstoken');

    }

    

    public function setToken($value)

    {

    	if (is_array($value) && !array_key_exists('expires_in', $value) && array_key_exists('expires', $value))

    	{

    		$value['expires_in'] = $value['expires'];

    		unset($value['expires']);

    	}

    	$this->setOption('accesstoken', $value);

    	return $this;

    }

    

    public function refreshToken($token = null)

    {

    	if (!$this->getOption('userefresh'))

    	{

    		throw new RuntimeException('Refresh token is not supported for this OAuth instance.');

    	}

    	if (!$token)

    	{

    		$token = $this->getToken();

    		if (!array_key_exists('refresh_token', $token))

    		{

    			throw new RuntimeException('No refresh token is available.');

    		}

    		$token = $token['refresh_token'];

    	}

    	$data['grant_type'] = 'refresh_token';

    	$data['refresh_token'] = $token;

    	$data['client_id'] = $this->getOption('clientid');

    	$data['client_secret'] = $this->getOption('clientsecret');

    	//$response = $this->http->post($this->getOption('tokenurl'), $data);

    	$response_array = wp_remote_post($this->getOption('tokenurl'),$data);

    	$response = $this->toObject($response_array);

    	

    	if ($response->code >= 200 || $response->code < 400)

    	{

    		if ($response->headers['Content-Type'] == 'application/json')

    		{

    			$token = array_merge(json_decode($response->body, true), array('created' => time()));

    		}

    		else

    		{

    			parse_str($response->body, $token);

    			$token = array_merge($token, array('created' => time()));

    		}

    		$this->setToken($token);

    		return $token;

    	}

    	else

    	{

    		throw new Exception('Error code ' . $response->code . ' received refreshing token: ' . $response->body . '.');

    	}

    }

    

    public function createUrl()

    {

    	if (!$this->getOption('authurl') || !$this->getOption('clientid'))

    	{

    		throw new InvalidArgumentException('Authorization URL and client_id are required');

    	}

    	$url = $this->getOption('authurl');

    	if (strpos($url, '?'))

    	{

    		$url .= '&';

    	}

    	else

    	{

    		$url .= '?';

    	}

    	$url .= 'response_type=code';

    	$url .= '&client_id=' . urlencode($this->getOption('clientid'));

    	if ($this->getOption('redirecturi'))

    	{

    		$url .= '&redirect_uri=' . urlencode($this->getOption('redirecturi'));

    	}

    	if ($this->getOption('scope'))

    	{

    		$scope = is_array($this->getOption('scope')) ? implode(' ', $this->getOption('scope')) : $this->getOption('scope');

    		$url .= '&scope=' . urlencode($scope);

    	}

    	if ($this->getOption('state'))

    	{

    		$url .= '&state=' . urlencode($this->getOption('state'));

    	}

    	if (is_array($this->getOption('requestparams')))

    	{

    		foreach ($this->getOption('requestparams') as $key => $value)

    		{

    			$url .= '&' . $key . '=' . urlencode($value);

    		}

    	}

    	return $url;

    }

    

    protected function toObject($a)

    {

    	    if (is_array($a) ) 

    	    {

        foreach($a as $k => $v) 

        {

            if (is_integer($k))

             {

                // only need this if you want to keep the array indexes separate

                // from the object notation: eg. $o->{1}

                //$a['index'][$k] = $this->toObject($v);

            }

            else {

                $a[$k] = $this->toObject($v);

            }

        }



        return (object) $a;

               }



    // else maintain the type of $a

    return $a;

    }

    

    public function isAuthenticated()

    {

    	$token = $this->getToken();

    	if (!$token || !array_key_exists('access_token', $token))

    	{

    		return false;

    	}

    	elseif (array_key_exists('expires_in', $token) && $token['created'] + $token['expires_in'] < time() + 20)

    	{

    		return false;

    	}

    	else

    	{

    		return true;

    	}

    }

}







?>