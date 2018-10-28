# think-seaslog

ThinkPHP 5.0 SeasLog 日志扩展

## 安装

~~~
composer require topthink/think-seaslog
~~~

## 配置

日志配置文件的type参数设置为 seaslog

目前仅支持的配置参数

|参数|描述|
|---|---|
| path |基础目录|
| logger |设置logger名称|
| json |是否使用JSON格式|

由于目前seaslog不支持动态配置，因此其它配置请在php.ini 中配置。

注意：如果开启JSON格式记录，请务必设置default_template参数 仅记录日志内容

~~~
 seaslog.default_template = "%M"
~~~

