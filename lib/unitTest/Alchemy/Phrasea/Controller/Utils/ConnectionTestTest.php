<?php

require_once __DIR__ . '/../../../../PhraseanetWebTestCaseAbstract.class.inc';

/**
 * Test class for ConnectionTest.
 * Generated by PHPUnit on 2012-01-11 at 18:20:20.
 */
class ControllerConnectionTestTest extends \PhraseanetWebTestCaseAbstract
{

  /**
   * As controllers use WebTestCase, it requires a client
   */
  protected $client;

  /**
   * If the controller tests require some records, specify it her
   *
   * For example, this will loacd 2 records
   * (self::$record_1 and self::$record_2) :
   *
   * $need_records = 2;
   *
   */
  protected static $need_records = false;

  /**
   * The application loader
   */
  public function createApplication()
  {
    return require __DIR__ . '/../../../../../Alchemy/Phrasea/Application/Admin.php';
  }

  public function setUp()
  {
    parent::setUp();
    $this->client = $this->createClient();
  }

  /**
   * Default route test
   */
  public function testRouteMysql()
  {
    $handler = new \Alchemy\Phrasea\Core\Configuration\Handler(
                    new \Alchemy\Phrasea\Core\Configuration\Application(),
                    new \Alchemy\Phrasea\Core\Configuration\Parser\Yaml()
    );
    $configuration = new \Alchemy\Phrasea\Core\Configuration($handler);

    $chooseConnexion = $configuration->getPhraseanet()->get('database');

    $connexion = $configuration->getConnexion($chooseConnexion);

    $params = array(
        "hostname" => $connexion->get('host'),
        "port" => $connexion->get('port'),
        "user" => $connexion->get('user'),
        "password" => $connexion->get('password'),
        "dbname" => $connexion->get('dbname')
    );

    $this->client->request("GET", "/tests/connection/mysql/", $params);
    $response = $this->client->getResponse();
    $this->assertTrue($response->isOk());
  }

  public function testRouteMysqlFailed()
  {
    $handler = new \Alchemy\Phrasea\Core\Configuration\Handler(
                    new \Alchemy\Phrasea\Core\Configuration\Application(),
                    new \Alchemy\Phrasea\Core\Configuration\Parser\Yaml()
    );
    $configuration = new \Alchemy\Phrasea\Core\Configuration($handler);

    $chooseConnexion = $configuration->getPhraseanet()->get('database');

    $connexion = $configuration->getConnexion($chooseConnexion);

    $params = array(
        "hostname" => $connexion->get('host'),
        "port" => $connexion->get('port'),
        "user" => $connexion->get('user'),
        "password" => "fakepassword",
        "dbname" => $connexion->get('dbname')
    );

    $this->client->request("GET", "/tests/connection/mysql/", $params);
    $response = $this->client->getResponse();
    $content = json_decode($this->client->getResponse()->getContent());
    $this->assertEquals("application/json", $this->client->getResponse()->headers->get("content-type"));
    $this->assertTrue($response->isOk());
    $this->assertTrue(is_object($content));
    $this->assertObjectHasAttribute('connection', $content);
    $this->assertObjectHasAttribute('database', $content);
    $this->assertObjectHasAttribute('is_empty', $content);
    $this->assertObjectHasAttribute('is_appbox', $content);
    $this->assertObjectHasAttribute('is_databox', $content);
    $this->assertFalse($content->connection);
  }

    public function testRouteMysqlDbFailed()
  {
    $handler = new \Alchemy\Phrasea\Core\Configuration\Handler(
                    new \Alchemy\Phrasea\Core\Configuration\Application(),
                    new \Alchemy\Phrasea\Core\Configuration\Parser\Yaml()
    );
    $configuration = new \Alchemy\Phrasea\Core\Configuration($handler);

    $chooseConnexion = $configuration->getPhraseanet()->get('database');

    $connexion = $configuration->getConnexion($chooseConnexion);

    $params = array(
        "hostname" => $connexion->get('host'),
        "port" => $connexion->get('port'),
        "user" => $connexion->get('user'),
        "password" => $connexion->get('password'),
        "dbname" => "fake-DTABASE-name"
    );

    $this->client->request("GET", "/tests/connection/mysql/", $params);
    $response = $this->client->getResponse();
    $content = json_decode($this->client->getResponse()->getContent());
    $this->assertEquals("application/json", $this->client->getResponse()->headers->get("content-type"));
    $this->assertTrue($response->isOk());
    $this->assertTrue(is_object($content));
    $this->assertObjectHasAttribute('connection', $content);
    $this->assertObjectHasAttribute('database', $content);
    $this->assertObjectHasAttribute('is_empty', $content);
    $this->assertObjectHasAttribute('is_appbox', $content);
    $this->assertObjectHasAttribute('is_databox', $content);
    $this->assertFalse($content->database);
  }

}

