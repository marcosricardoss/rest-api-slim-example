<?php

use Slim\Http\Environment;
use Slim\Http\Request;
use org\bovigo\vfs\vfsStream;
use Marcosricardoss\Restful\App;
use Marcosricardoss\Restful\User;

require_once 'TestDatabasePopulator.php';

class RestfulApiTest extends PHPUnit_Framework_TestCase {

    protected $app;
    protected $user;
    protected $registerErrorMessage;    

    public function setUp() {
        $root = vfsStream::setup();
        $envFilePath = vfsStream::newFile('.env')->at($root);
        $envFilePath->setContent('
            APP_SECRET=secretKey 
            JWT_ALGORITHM=HS256
            [Database]
            driver=mysql
            host=127.0.0.1
            username=root
            password=123
            port=3306
            charset=utf8
            collation=utf8_unicode_ci
            database=restfulapi
            ');
        $this->app = (new App($root->url()))->get();
        $this->user = TestDatabasePopulator::populate();
        $this->registerErrorMessage = 'Username or Password field not provided.';
    }

    protected function get($url) {
        $env = Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI'    => $url,
            ]);
        $req = Request::createFromEnvironment($env);
        $this->app->getContainer()['request'] = $req;
        return $this->app->run(true);
    }

    protected function post($url, $body) {
        $env = Environment::mock([
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI'    => $url,
            'CONTENT_TYPE'   => 'application/x-www-form-urlencoded',
        ]);
        $req = Request::createFromEnvironment($env)->withParsedBody($body);
        $this->app->getContainer()['request'] = $req;
        return $this->app->run(true);
    }

    public function testPHPUnitWarningSuppressor() {
        $this->assertTrue(true);
    }

    protected function getLoginTokenForTestUser() {
        $response = $this->post('/auth/login', ['username' => $this->user->username, 'password' => $this->user->password]);
        $result = json_decode($response->getBody(), true);
        return $result['token'];
    }

}