<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace HyperfTest\Cases;

use App\Kernel\Context\Coroutine;
use App\Kernel\Log\AppendRequestIdProcessor;
use Hyperf\Engine\Channel;
use Hyperf\Utils\Context;
use HyperfTest\HttpTestCase;

/**
 * @internal
 * @coversNothing
 */
class ExampleTest extends HttpTestCase
{
    public function testExample()
    {
        $this->assertTrue(true);

        $res = $this->get('/');

        $this->assertSame(0, $res['code']);
        $this->assertSame('Hello Hyperf.', $res['data']['message']);
        $this->assertSame('GET', $res['data']['method']);
        $this->assertSame('Hyperf', $res['data']['user']);

        $res = $this->get('/', ['user' => 'limx']);

        $this->assertSame(0, $res['code']);
        $this->assertSame('limx', $res['data']['user']);

        $res = $this->post('/', [
            'user' => 'limx',
        ]);
        $this->assertSame('Hello Hyperf.', $res['data']['message']);
        $this->assertSame('POST', $res['data']['method']);
        $this->assertSame('limx', $res['data']['user']);

        Context::set(AppendRequestIdProcessor::REQUEST_ID, $id = uniqid());
        $pool = new Channel(1);
        di()->get(Coroutine::class)->create(function () use ($pool) {
            try {
                $all = Context::getContainer();
                $pool->push((array) $all);
            } catch (\Throwable $exception) {
                $pool->push(false);
            }
        });

        $data = $pool->pop();
        $this->assertIsArray($data);
        $this->assertSame($id, $data[AppendRequestIdProcessor::REQUEST_ID]);
    }
}
