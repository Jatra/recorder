<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';
require '../private/dbconfig.php';

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

//$config['db']['host']   = 'localhost';
//$config['db']['user']   = 'db user'
//$config['db']['pass']   = 'db user passwd';
//$config['db']['dbname'] = 'dbname';

$app = new \Slim\App(['settings' => $config]);

$container = $app->getContainer();

$container['db'] = function ($c) {
    $db = $c['settings']['db'];
    $pdo = new PDO('mysql:host=' . $db['host'] . ';dbname=' . $db['dbname'],
        $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
};

$app->group('/api', function () use ($app) {
    // Version group
    $app->group('/v1', function () use ($app) {
		$app->get('/users', function (Request $request, Response $response, array $args) {
   			$sql = "select * FROM user";
    			try {
				$stmt = $this->db->query($sql);
        			$wines = $stmt->fetchAll(PDO::FETCH_OBJ);
        			$response->getBody()->write(json_encode($wines));
    			} catch(PDOException $e) {
        			$response->getBody()->write('{"error":{"text":'. $e->getMessage() .'}}');
			}
		});
		$app->get('/user/{id}', function (Request $request, Response $response, array $args) {
			$id = $request->getAttribute('id');
   			$sql = "select * FROM user WHERE id=:id";
    			try {
				$stmt = $this->db->prepare($sql);
				$stmt->bindParam(id, $id);
				$stmt->execute();
				if ($user = $stmt->fetch()) {
					$response->getBody()->write(json_encode($user));
				} else {
					return $response->withStatus(404);
				}
    			} catch(PDOException $e) {
        			$response->getBody()->write('{"error":{"text":'. $e->getMessage() .'}}');
			}
		});

		$app->get('/events', function (Request $request, Response $response, array $args) {
   			$sql = "select * FROM event";
    			try {
				$stmt = $this->db->query($sql);
        			$wines = $stmt->fetchAll(PDO::FETCH_OBJ);
        			$response->getBody()->write(json_encode($wines));
    			} catch(PDOException $e) {
        			$response->getBody()->write('{"error":{"text":'. $e->getMessage() .'}}');
			}
		});
		$app->get('/event/{id}', function (Request $request, Response $response, array $args) {
			$id = $request->getAttribute('id');
   			$sql = "select * FROM event WHERE id=:id";
    			try {
				$stmt = $this->db->prepare($sql);
				$stmt->bindParam(id, $id);
		        	$stmt->execute();
				$event = $stmt->fetch();
				$response->getBody()->write(json_encode($event));
    			} catch(PDOException $e) {
        			$response->getBody()->write('{"error":{"text":'. $e->getMessage() .'}}');
			}
		});
		$app->get('/occurences', function (Request $request, Response $response, array $args) {
   			$sql = "select * FROM occurence";
    			try {
				$stmt = $this->db->query($sql);
        			$wines = $stmt->fetchAll(PDO::FETCH_OBJ);
        			$response->getBody()->write(json_encode($wines));
    			} catch(PDOException $e) {
        			$response->getBody()->write('{"error":{"text":'. $e->getMessage() .'}}');
			}
		});
		$app->get('/occurence/{id}', function (Request $request, Response $response, array $args) {
			$id = $request->getAttribute('id');
   			$sql = "select * FROM occurence WHERE id=:id";
    			try {
				$stmt = $this->db->prepare($sql);
				$stmt->bindParam(id, $id);
		        	$stmt->execute();
				$occurence = $stmt->fetch();
				$response->getBody()->write(json_encode($occurence));
    			} catch(PDOException $e) {
        			$response->getBody()->write('{"error":{"text":'. $e->getMessage() .'}}');
			}
		});
		$app->post('/event', function(Request $request, Response $response, array $args) {
			$sql = "INSERT INTO event (event_name, event_description) VALUES (:name, :description)";
			$event = json_decode($request->getBody());
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(name, $event->event_name);
			$stmt->bindParam(description, $event->event_description);
		        $stmt->execute();
		        $event->id = $this->db->lastInsertId();
			$response->getBody()->write(json_encode($event));
		});
		$app->post('/occurence', function(Request $request, Response $response, array $args) {
			$sql = "INSERT INTO occurence (eventId, userId) VALUES (:eventId, :userId)";
			$occurence = json_decode($request->getBody());
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(eventId, $occurence->eventId);
			$stmt->bindParam(userId, $occurence->userId);
		        $stmt->execute();
		        $occurence->id = $this->db->lastInsertId();
			$response->getBody()->write(json_encode($occurence));
		});
	});
});


$app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
    $name = $args['name'];
    $response->getBody()->write("Hello, $name");

    return $response;
});

$app->run();
