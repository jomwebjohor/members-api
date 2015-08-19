<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
require 'vendor/autoload.php';

$dataSourceName = "mysql:dbname=member;host=localhost";
$username = "homestead";
$password = "secret";

$pdo = new PDO($dataSourceName, $username, $password);
$db = new NotORM($pdo);

$app = new \Slim\Slim();

$app->view(new \JsonApiView());
$app->add(new \JsonApiMiddleware());

define('secret', 'secret');

$app->add(new \Slim\Middleware\JwtAuthentication(array(
    'secure' => false,
    'path' => '/api/members',
    'secret' => constant('secret')
)));

$app->config('debug', false);

$app->get('/', function() use ($app) {
    $app->render(200, array(
        'msg' => 'Welcome to Jom Web Johor!',
    ));
});

$app->get('/jwj', function() use ($app) {

    $key = constant('secret');
    $token = array(
        "facebook" => "https://facebook.com/groups/jomwebjohor",
        "github" => "https://github.com/jomwebjohor"
    );

    $jwt = JWT::encode($token, $key);
    $app->render(200, array(
        'token' => $jwt
    ));
});

$app->get('/api', function() use ($app) {
    $app->render(200, array(
        'msg' => 'Welcome to my json API!',
    ));
});

$app->get('/api/members/:username', function($username) use ($app, $db) {

    $result = $db->members()->where('name like ?', "%$username%")
                ->or('facebook like ?', "%$username%")
                ->or('twitter like ?', "%$username%")
                ->or('github like ?', "%$username%")
                ->or('telegram like ?', "%$username%")
                ->limit(1);

    $member = $result->fetch();

    if ($member) {

        $skills = $member['skills'] ? explode(',', $member['skills']) : array();

        $app->render(200, array(
            'name' => $member['name'],
            'location' => $member['location'],
            'position' => $member['position'],
            'company' => $member['company'],
            'skills' => $skills,
            'social' => array(
                'facebook' => array(
                    'username' => $member['facebook'],
                    'uri' => $member['facebook'] ? 'https://facebook.com/' . $member['facebook'] : null
                ),
                'twitter' => array(
                    'username' => $member['twitter'],
                    'uri' => $member['twitter'] ? 'https://twitter.com/' . $member['twitter'] : null
                ),
                'github' => array(
                    'username' => $member['github'],
                    'uri' => $member['github'] ? 'https://github.com/' . $member['github'] : null
                ),
                'telegram' => $member['telegram']
            )
        ));
    } else {

        $app->render(404, array(
            'error' => true,
            'msg'   => 'Member not found'
        ));
    }
});

$app->post("/api/members", function () use($app, $db) {

    $post = $app->request()->post();

    if (!$post['name']) {
        $app->render(400, array(
            'error' => true,
            'msg'   => 'Invalid name.'
        ));
    }

    if ($db->members->insert($post)) {

        $skills = $post['skills'] ? explode(',', $post['skills']) : array();

        $app->render(200, array(
            'name' => $post['name'],
            'location' => $post['location'],
            'position' => $post['position'],
            'company' => $post['company'],
            'skills' => $skills,
            'social' => array(
                'facebook' => array(
                    'username' => $post['facebook'],
                    'uri' => $post['facebook'] ? 'https://facebook.com/' . $post['facebook'] : null
                ),
                'twitter' => array(
                    'username' => $post['twitter'],
                    'uri' => $post['twitter'] ? 'https://twitter.com/' . $post['twitter'] : null
                ),
                'github' => array(
                    'username' => $post['github'],
                    'uri' => $post['github'] ? 'https://github.com/' . $post['github'] : null
                ),
                'telegram' => $post['telegram']
            )
        ));
    } else {

        $app->render(400, array(
            'error' => true,
            'msg'   => 'Error occured while trying to save the record.'
        ));
    }
});

$app->put("/api/members/:id", function ($id) use ($app, $db) {

    $member = $db->members()->where('id', $id);

    if ($member->fetch()) {
        $put = $app->request()->put();

        foreach ($put as $k => $v) {
            if (!$put[$k]) {
                unset($put[$k]);
            }
        }

        if ($member->update($put)) {

            $skills = $put['skills'] ? explode(',', $put['skills']) : array();

            $app->render(200, array(
                'name' => $put['name'],
                'location' => $put['location'],
                'position' => $put['position'],
                'company' => $put['company'],
                'skills' => $skills,
                'social' => array(
                    'facebook' => array(
                        'username' => $put['facebook'],
                        'uri' => $put['facebook'] ? 'https://facebook.com/' . $put['facebook'] : null
                    ),
                    'twitter' => array(
                        'username' => $put['twitter'],
                        'uri' => $put['twitter'] ? 'https://twitter.com/' . $put['twitter'] : null
                    ),
                    'github' => array(
                        'username' => $put['github'],
                        'uri' => $put['github'] ? 'https://github.com/' . $put['github'] : null
                    ),
                    'telegram' => $put['telegram']
                )
            ));
        } else {

            $app->render(400, array(
                'error' => true,
                'msg'   => 'Error occured while trying to save the record.'
            ));
        }
    } else {

        $app->render(404, array(
            'error' => true,
            'msg'   => 'Invalid Id.'
        ));
    }
});

$app->run();