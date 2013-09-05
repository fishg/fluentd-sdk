#!/usr/bin/env python
# -*- coding: utf-8 -*-
# 
__authors__ = [
  '"肥西" <yujunpeng@zhubajie.com>',
]
from zbj_logger import ZBJLogger
#配置,建议定义到项目统一config文件
host       = '127.0.0.1'
port       = '24224'
tag        = 'sdk.python'
#模块标识配置,建议每个类单独配置
tag_module = 'test'

zbj_logger = ZBJLogger(tag, host, port);
zbj_logger.log(tag_module ,{
    'client_ip':'127.0.0.1',        #客户端 ip,如果是 http或者其他网络服务的请求,请传入用户的真实 ip
    'status':0,                     #整数,操作状态:0表示没有异常,其他值表示异常
    'level' :0,                     #整数,对应各种日志级别,基本日志级别:0 info级别,10 warning级别,20 error级别
    'service':'fluentd.python.sdk', #服务标识,比如登陆功能:login
    'user_info' : {                 #用户信息,自定义字段
        'id'  : 123,
        'name': '王大爷python',
        },
    'logmsg':{                       #日志主体内容,字段自定义
        'uname':'zhangzhan'
        },
    'userRequest':{'get':{},'post':{},'cookie':{},'server':{}}, #如果是 http 服务,在出错的情况下建议记录该日志
});
