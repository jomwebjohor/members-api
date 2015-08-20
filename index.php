<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
require 'vendor/autoload.php';

$db = new PDO('sqlite:db.sqlite3');

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

    $query = $db->prepare("SELECT * FROM members WHERE name LIKE ? OR facebook LIKE ? OR twitter LIKE ? OR github LIKE ? OR telegram LIKE ? LIMIT 1;");
    $query->execute(array(
        "%$username%",
        "%$username%",
        "%$username%",
        "%$username%",
        "%$username%"
    ));

    $member = array_shift($query->fetchAll(PDO::FETCH_CLASS));

    if ($member) {

        $skills = $member->skills ? explode(',', $member->skills) : array();

        $app->render(200, array(
            'id' => (int)$member->id,
            'name' => $member->name,
            'location' => $member->location,
            'position' => $member->position,
            'company' => $member->company,
            'skills' => $skills,
            'social' => array(
                'facebook' => array(
                    'username' => $member->facebook,
                    'uri' => $member->facebook ? 'https://facebook.com/' . $member->facebook : null
                ),
                'twitter' => array(
                    'username' => $member->twitter,
                    'uri' => $member->twitter ? 'https://twitter.com/' . $member->twitter : null
                ),
                'github' => array(
                    'username' => $member->github,
                    'uri' => $member->github ? 'https://github.com/' . $member->github : null
                ),
                'telegram' => $member->telegram
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

    $skills = $post['skills'] ? explode(',', $post['skills']) : array();

    $query = $db->prepare('INSERT INTO members (name, location, position, skills, facebook, twitter, github, telegram) '.
             'VALUES (?, ?, ?, ?, ?, ?, ?, ?);');
    $query->execute(array(
        $post['name'],
        $post['location'],
        $post['position'],
        $skills,
        $post['facebook'],
        $post['twitter'],
        $post['github'],
        $post['telegram']
    ));

    if ($query->rowCount() == 1) {

        $app->render(200, array(
            'id' => (int)$db->lastInsertId(),
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

    $query = $db->prepare('SELECT * FROM members WHERE id = ? LIMIT 1;');
    $query->execute(array(intval($id)));

    $member = array_shift($query->fetchAll(PDO::FETCH_CLASS));

    if ($member) {
        $put = $app->request()->put();

        foreach ($put as $k => $v) {
            if ($k == 'name') {
                $name = ", name  = '" . $put['name'] . "'";
            }
            if ($k == 'location') {
                $location = ", location  = '" . $put['location'] . "'";
            }
            if ($k == 'position') {
                $position = ", position  = '" . $put['position'] . "'";
            }
            if ($k == 'company') {
                $company = ", company  = '" . $put['company'] . "'";
            }
            if ($k == 'skills') {
                $skills = ", skills  = '" . $put['skills'] . "'";
            }
            if ($k == 'facebook') {
                $facebook = ", facebook  = '" . $put['facebook'] . "'";
            }
            if ($k == 'twitter') {
                $twitter = ", twitter  = '" . $put['twitter'] . "'";
            }
            if ($k == 'github') {
                $github = ", github  = '" . $put['github'] . "'";
            }
            if ($k == 'telegram') {
                $telegram = ", telegram  = '" . $put['telegram'] . "'";
            }
        }

        $query = $db->prepare("UPDATE members SET id = $id $name $location $position $company $skills $facebook $twitter $github $telegram WHERE id = ?;");
        $query->execute(array(intval($id)));

        if ($query->rowCount() == 1) {

            $query = $db->prepare('SELECT * FROM members WHERE id = ? LIMIT 1;');
            $query->execute(array(intval($id)));

            $member = array_shift($query->fetchAll(PDO::FETCH_CLASS));

            $skills = $member->skills ? explode(',', $member->skills) : array();

            $app->render(200, array(
                'id' => (int)$member->id,
                'name' => $member->name,
                'location' => $member->location,
                'position' => $member->position,
                'company' => $member->company,
                'skills' => $skills,
                'social' => array(
                    'facebook' => array(
                        'username' => $member->facebook,
                        'uri' => $member->facebook ? 'https://facebook.com/' . $member->facebook : null
                    ),
                    'twitter' => array(
                        'username' => $member->twitter,
                        'uri' => $member->twitter ? 'https://twitter.com/' . $member->twitter : null
                    ),
                    'github' => array(
                        'username' => $member->github,
                        'uri' => $member->github ? 'https://github.com/' . $member->github : null
                    ),
                    'telegram' => $member->telegram
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

$app->get('/install', function () use ($app, $db) {

    $db->exec('CREATE TABLE IF NOT EXISTS members (
                    id INTEGER PRIMARY KEY,
                    name TEXT,
                    location TEXT,
                    position TEXT,
                    company TEXT,
                    skills TEXT,
                    facebook TEXT,
                    twitter TEXT,
                    github TEXT,
                    telegram TEXT);');

    $app->render(200, array(
        'error' => false,
        'msg'   => 'Database is ready!'
    ));
});

$app->run();