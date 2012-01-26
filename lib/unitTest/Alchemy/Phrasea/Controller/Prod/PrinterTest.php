<?php

require_once __DIR__ . '/../../../../PhraseanetWebTestCaseAuthenticatedAbstract.class.inc';

/**
 * Test class for Printer.
 * Generated by PHPUnit on 2012-01-11 at 18:24:29.
 */
class ControllerPrinterTest extends \PhraseanetWebTestCaseAuthenticatedAbstract
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
  protected static $need_records = 2;

  /**
   * The application loader
   */
  public function createApplication()
  {
    return require __DIR__ . '/../../../../../Alchemy/Phrasea/Application/Prod.php';
  }

  public function setUp()
  {
    parent::setUp();
    $this->client = $this->createClient();
  }

  /**
   * Default route test
   */
  public function testRouteSlash()
  {
    $records = array(
        self::$record_1->get_serialize_key(),
        self::$record_2->get_serialize_key()
    );

    $lst = implode(';', $records);

    $crawler = $this->client->request('POST', '/printer/', array('lst' => $lst));

    $response = $this->client->getResponse();

    $this->assertTrue($response->isOk());
  }

  public function testRoutePrintPdf()
  {

    $this->markTestSkipped("Undefined variable: k_path_url \n /Users/nicolasl/workspace/phraseanet/lib/vendor/tcpdf/config/tcpdf_config.php:75");

    $records = array(
        self::$record_1->get_serialize_key(),
        self::$record_2->get_serialize_key()
    );

    $lst = implode(';', $records);

    $crawler = $this->client->request('POST', '/printer/print.pdf', array(
        'lst' => $lst,
        'lay' => \Alchemy\Phrasea\Out\Module\PDF::LAYOUT_PREVIEW
            )
    );

    $response = $this->client->getResponse();

    $this->assertEquals("application/pdf", $response->headers->get("content-type"));

    $this->assertTrue($response->isOk());
  }

}
