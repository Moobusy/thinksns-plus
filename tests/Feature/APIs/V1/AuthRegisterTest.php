<?php

namespace Tests\Feature\APIs\V1;

use Zhiyi\Plus\Models\User;

class AuthRegisterTest extends TestCase
{
    // test API.
    protected $uri = '/api/v1/auth/register';

    // Set up create user.
    protected $user;

    // test user data.
    protected $phone = '18781994583';
    protected $password = 123456;
    protected $username = 'Seven_test_user';

    // send test request body.
    protected $requestBody = [];

    /**
     * Setup the test environment.
     *
     * @author Seven Du <shiweidu@outlook.com>
     * @homepage http://medz.cn
     */
    protected function setUp()
    {
        parent::setUp();

        $phone = $this->phone;
        $name = $this->username;

        User::where('phone', $phone)
            ->orWhere('name', $name)
            ->withTrashed()
            ->forceDelete();

        // Create user.
        $user = new User();
        $user->phone = $phone;
        $user->name = $name;
        $user->email = '';
        $user->createPassword($this->password);
        $user->save();

        $this->user = $user;
        $this->requestBody = [
            'phone'       => $phone,
            'password'    => $this->password,
            'device_code' => 'testing',
        ];
    }

    /**
     * Clean up the testing environment before the next test.
     *
     * @author Seven Du <shiweidu@outlook.com>
     * @homepage http://medz.cn
     */
    protected function tearDown()
    {
        // delete user.
        $this->user->forceDelete();
        parent::tearDown();
    }

    /**
     * 测试当设备号为空的时候的报错信息.
     *
     * message code: 1014
     *
     * test middleware \App\Http\Middleware\CheckDeviceCodeExisted
     *
     * @return [type] [description]
     *
     * @author Seven Du <shiweidu@outlook.com>
     * @homepage http://medz.cn
     */
    public function testCheckDeviceCodeNotExisted()
    {
        $requestBody = $this->requestBody;
        $requestBody['device_code'] = '';

        $response = $this->postJson($this->uri, $requestBody);

        // Asserts that the status code of the response matches the given code.
        $response->assertStatus(422);

        // Assert that the response contains an exact JSON array.
        $json = static::createJsonData([
            'code'    => 1014,
            'message' => '设备号不能为空',
        ]);
        $response->assertJson($json);
    }

    /**
     * 测试注册手机号为空.
     *
     * message code:1000
     * test middleware \App\Http\Middleware\VerifyPhoneNumber.
     *
     * @author martinsun <syh@sunyonghong.com>
     * @datetime 2017-01-05T11:31:03+080
     *
     * @version  1.0
     */
    public function testCheckPhoneNotExisted()
    {
        $requestBody = $this->requestBody;
        $requestBody['phone'] = '';

        $response = $this->postJson($this->uri, $requestBody);

        // Asserts that the status code of the response matches the given code.
        $response->assertStatus(403);

        // Assert that the response contains an exact JSON array.
        $json = static::createJsonData([
            'code' => 1000,
        ]);
        $response->assertJson($json);
    }

    /**
     * 测试注册手机号非法.
     *
     * message code:1000
     * test middleware \App\Http\Middleware\VerifyPhoneNumber.
     *
     * @author martinsun <syh@sunyonghong.com>
     * @datetime 2017-01-05T11:32:58+080
     *
     * @version  1.0
     */
    public function testCheckPhoneError()
    {
        $requestBody = $this->requestBody;
        $requestBody['phone'] = '123456789';

        $response = $this->postJson($this->uri, $requestBody);
        // Asserts that the status code of the response matches the given code.
        $response->assertStatus(403);
        // Assert that the response contains an exact JSON array.
        $json = static::createJsonData([
            'code' => 1000,
        ]);
        $response->assertJson($json);
    }

    /**
     * 测试注册用户名不存在.
     *
     * message code:1000
     * test middleware \App\Http\Middleware\VerifyUserNameRole.
     *
     * @author martinsun <syh@sunyonghong.com>
     * @datetime 2017-01-05T11:34:03+080
     *
     * @version  1.0
     */
    public function testCheckUserNameNotExisted()
    {
        $requestBody = $this->requestBody;

        $response = $this->postJson($this->uri, $requestBody);
        // Asserts that the status code of the response matches the given code.
        $response->assertStatus(403);
        // Assert that the response contains an exact JSON array.
        $json = static::createJsonData([
            'code' => 1002,
        ]);
        $response->assertJson($json);
    }

    /**
     * 测试注册用户名长度.
     *
     * message code:403
     * test middleware \App\Http\Middleware\VerifyUserNameRole.
     *
     * @author martinsun <syh@sunyonghong.com>
     * @datetime 2017-01-05T11:34:43+080
     *
     * @version  1.0
     */
    public function testCheckUserNameLength()
    {
        $requestBody = $this->requestBody;
        $requestBody['name'] = 'iss';

        $response = $this->postJson($this->uri, $requestBody);
        // Asserts that the status code of the response matches the given code.
        $response->assertStatus(403);
        // Assert that the response contains an exact JSON array.
        $json = static::createJsonData([
            'code' => 1002,
        ]);
        $response->assertJson($json);
    }

    /**
     * 测试注册用户名规则.
     *
     * message code:1003
     * test middleware \App\Http\Middleware\VerifyUserNameRole
     *
     * @author martinsun <syh@sunyonghong.com>
     * @datetime 2017-01-05T11:34:43+080
     *
     * @version  1.0
     */
    public function testCheckUserNameRole()
    {
        $requestBody = $this->requestBody;
        $requestBody['name'] = '++test';

        $response = $this->postJson($this->uri, $requestBody);
        // Asserts that the status code of the response matches the given code.
        $response->assertStatus(403);
        // Assert that the response contains an exact JSON array.
        $json = static::createJsonData([
            'code' => 1003,
        ]);
        $response->assertJson($json);
    }

    /**
     * 测试注册时用户名是否被占用.
     *
     * message code:1004
     * test middleware \App\Http\Middleware\CheckUserByNameNotExisted
     *
     * @author martinsun <syh@sunyonghong.com>
     * @datetime 2017-01-05T11:34:43+080
     *
     * @version  1.0
     */
    public function testCheckUserNameUsed()
    {
        $requestBody = $this->requestBody;
        $requestBody['name'] = 'Seven_test_user';

        $response = $this->postJson($this->uri, $requestBody);
        // Asserts that the status code of the response matches the given code.
        $response->assertStatus(403);
        // Assert that the response contains an exact JSON array.
        $json = static::createJsonData([
            'code' => 1004,
        ]);
        $response->assertJson($json);
    }
}
