<?php
declare(strict_types=1);
namespace test\controller;
// phpunit --colors=always --bootstrap ./bootstrap/boot.php ./tests
use PHPUnit\Framework\TestCase;
use helper\Utility;
use Exception;

use function PHPUnit\Framework\assertNotEmpty;
use function PHPUnit\Framework\assertTrue;

class UserControllerTest extends TestCase{
	static $stub = null;
	protected function setUp(): void{
	}
	protected function tearDown(): void{
	}
	public static function setUpBeforeClass(): void{
	}
	public static function tearDownAfterClass(): void{
		if(self::$stub){
			$curlOpt = [
				CURLOPT_CUSTOMREQUEST => "DELETE",	
			];
			Utility::curl('http://localhost:5000/users/'.self::$stub,[],true,$curlOpt);
		}
	}
	function testCreate(){
		$data = array(
			'name' => 'TestName',
			'email' => 'testmail9@example.com',
			'phone' => '0800000009',
			'password'=> 'testpassword'
		);
		$payload = json_encode($data);
		$curlOpt = [
			CURLOPT_POSTFIELDS=>$payload,
			CURLOPT_POST => true,
			CURLINFO_HEADER_OUT => true,
			CURLOPT_HTTPHEADER => [
				'Content-Type: application/json',
				'Content-Length: ' . strlen($payload)]
		];
		$response = Utility::curl('http://localhost:5000/users',[],true,$curlOpt);
		// fwrite(STDOUT,__METHOD__.'\n');
		self::assertArrayHasKey('data',$response,'data array check');
		self::assertArrayHasKey('id',$response['data'],'Data has ID key');
		self::assertEquals(200,$response['response'],"Status code check");
		self::assertNotEmpty($response['data']['id'],'ID is present');
		self::assertEquals(13,strlen($response['data']['id']),'ID of right length, 13');
		self::$stub = $response['data']['id'];
	}
	function testGetAll(){
		$response = Utility::curl('http://localhost:5000/users',[]);
		$this->assertArrayHasKey('data',$response);
		$this->assertNotEmpty($response['data']);
		$this->assertNotEmpty($response['data'][0]);
		$this->assertEquals(13,strlen($response['data'][0]['id']));
	}
	function testGetById(){
		if(self::$stub){
			// fwrite(STDERR,self::$stub.' in getById');
			$response = Utility::curl('http://localhost:5000/users/'.self::$stub,[]);
			$this->assertArrayHasKey('data',$response);
			$this->assertNotEmpty($response['data']);
			$this->assertNotEmpty($response['data']['id']);
			$this->assertEquals(13,strlen($response['data']['id']));
			$this->assertEquals(self::$stub,$response['data']['id']);
		}else
			throw new Exception('Stub not initialized');
	}
	function testDelete(){
		if(self::$stub){
			$curlOpt = [
				 CURLOPT_CUSTOMREQUEST => "DELETE",	
			];
			$response = Utility::curl('http://localhost:5000/users/'.self::$stub,[],true,$curlOpt);
			$this->assertArrayHasKey('response',$response);
			$this->assertEquals(200,$response['response']);
			$this->assertArrayHasKey('data',$response);
			$this->assertEmpty($response['data']);
			self::$stub = null;
		}else
			throw new Exception('Stub not initialized');
	}
}