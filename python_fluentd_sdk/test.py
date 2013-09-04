#!/usr/bin/env python
# encoding: utf-8
# 
__authors__ = [
  '"肥西" <yujunpeng@zhubajie.com>',
]
from zbj_logger import ZBJLogger

tag        = 'projectname'
tag_module = ''
zbj_logger = ZBJLogger(tag,'192.168.5.103');

i = 0;
while i<20000:
	zbj_logger.log(tag_module ,{
		'client_ip':'127.0.0.1',
       	"status":0,
		"level" :0,
		"service":"fluentd.python.sdk",
		"user_info" : {
			"id"  : 123,
			"name": "王大爷python",
			},
		"logmsg":{
			"uname":"zhangzhan"
			},
		});
	i += 1;