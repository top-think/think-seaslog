<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace think\log\driver;

use SeasLog as SeasLogger;
use think\App;
use think\contract\LogHandlerInterface;

/**
 * 本地化调试输出到文件
 */
class Seaslog implements LogHandlerInterface
{
    /**
     * 配置参数
     * @var array
     */
    protected $config = [
        'time_format' => ' c ',
        'path'        => '',
        'logger'      => '',
        'json'        => false,
    ];

    /**
     * 应用对象
     * @var App
     */
    protected $app;

    // 实例化并传入参数
    public function __construct(App $app, array $config = [])
    {
        $this->app = $app;

        if (is_array($config)) {
            $this->config = array_merge($this->config, $config);
        }

        if (empty($this->config['path'])) {
            $this->config['path'] = $this->app->getRuntimePath() . 'log' . DIRECTORY_SEPARATOR;
        } elseif (substr($this->config['path'], -1) != DIRECTORY_SEPARATOR) {
            $this->config['path'] .= DIRECTORY_SEPARATOR;
        }

        SeasLogger::setBasePath($this->config['path']);

        if ($this->config['logger']) {
            SeasLogger::setLogger($this->config['logger']);
        }
    }

    /**
     * 日志写入接口
     * @access public
     * @param  array $log 日志信息
     * @return bool
     */
    public function save(array $log = []): bool
    {
        if (PHP_SAPI != 'cli') {
            if (!$this->config['json']) {
                SeasLogger::log('info', $this->parseLog());
            }
        }

        foreach ($log as $type => $val) {
            if ($this->config['json']) {
                $info[$type] = $val;
            } else {
                SeasLogger::log($type, $val);
            }
        }

        if (!empty($info)) {
            if (isset($info['info'])) {
                array_unshift($info['info'], $this->parseLog(true));
            } else {
                $info['info'][] = $this->parseLog(true);
            }

            SeasLogger::log('info', json_encode($info, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }

        return SeasLogger::flushBuffer();
    }

    /**
     * 追加请求日志
     * @access protected
     * @param  bool     $json 是否JSON格式
     * @return string
     */
    protected function parseLog(bool $json = false): string
    {
        $info = [
            'timestamp' => date($this->config['time_format']),
            'ip'        => $this->app['request']->ip(),
            'method'    => $this->app['request']->method(),
            'host'      => $this->app['request']->host(),
            'uri'       => $this->app['request']->url(),
        ];

        if ($json) {
            return json_encode($info, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return "---------------------------------------------------------------\r\n[{$info['timestamp']}] {$info['ip']} {$info['method']} {$info['host']}{$info['uri']}";
    }

}
