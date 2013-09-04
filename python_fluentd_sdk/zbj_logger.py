#!/usr/bin/env python
# encoding: utf-8
# 
# Copyright 2013 zhubajie.com.
# This script send log data to Fluentd-server.
# 
"""该 sdk 是猪八戒开放平台提供用于标准化和简化 Logger 系统接入。依赖fluent模块.
"""
__authors__ = [
  '"肥西" <yujunpeng@zhubajie.com>',
]

import socket
import fcntl
import struct
import time
import datetime
from random import randrange
from fluent import sender
from fluent import event

class ZBJLogger:
	#Todo: not implement
	message      = None
	error_msg    = None

	def __init__(self,tag,host='127.0.0.1',port=24224):
		self.host  = host
		self.port  = port
		self.tag   = tag
		self.msg   = {
			'uuid':self.get_uuid(), #规则:机器名-timestamp-rand(10000,99999)
			'level':0,                    #0/10/20,对应 info/warning/error
			'client_ip':'',         #用户访问 ip
			'category':'',          #项目标识
			'logtime':0,        #精确到秒的unix_timestamp
			'service':'',            #服务模块标识
			'status':0,                   #0表示操作成功,没有异常.其它值表示操作可能存在其他问题
			'machine':socket.gethostname(), #机器名
			'server_ip':self.get_ip_address('eth0'),#默认取eth0网卡的 ip 地址
			'user_info':{},          #用户信息
			'logmsg':{},        #自定义扩展字段
			'userRequest':{'get':{},'post':{},'cookie':{},'server':{}}, #用户发起的 http 请求信息,建议在记
			                                                            #录错误日志的时候记录该信息

		}
		sender.setup(self.tag, host=self.host, port=self.port)

	def log(self,child_tag,msg):
		"""把日志发送到中心节点.
		Args:
		msg: Dict,需要发送的日志内容,比如{
			'level':0,                            #0/10/20,对应 info/warning/error
			'client_ip':'',      		          #用户访问 ip
			'logtime':int(time.time()),           #精确到秒的unix_timestamp,默认取发送时的时间
			'service':'',                         #服务模块标识
			'status':0,                           #0表示操作成功,没有异常.其它值表示操作可能存在其他问题
			'server_ip':self.get_ip_address('eth0'),#服务器 ip,默认获取eth0的 IP
			'user_info':{},                       #用户信息
			'logmsg':{},                          #自定义扩展字段
			'user_request':{'get':{},'post':{},'cookie':{},'server':{}}, #用户发起的 http 请求信息
			}
		tag: String,日志分类标识,比如:projectname.user.login。

		Returns:
		log's uuid

		Raises:
		None
		"""
		assert isinstance(msg,dict), 'msg must be a dict.'
		if(not msg.has_key('logtime')):
			msg['logtime'] = int(time.time())
		self.msg.update(msg)
		#self.msg['tag']=self.tag + '.' + child_tag 
		event.Event(child_tag, self.msg)
		uuid = self.msg.get('uuid')
		self.msg.clear()
		return uuid

	def get_ip_address(self,ifname):
		"""通过指定网卡设备获取本机 ip,仅支持 linux
		"""
		try:
		    s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
		    ip= socket.inet_ntoa(fcntl.ioctl(
		        s.fileno(),
		        0x8915,  # SIOCGIFADDR
		        struct.pack('256s', ifname[:15])
		    )[20:24])
		except IOError:
			ip = ''
		return ip
	def get_uuid(self):
		"""获取唯一标识uuid,规则:机器名-timestamp-rand(10000,99999)
		"""
		now = datetime.datetime.now()
		return "%s-%s-%d"%(socket.gethostname(),now.strftime("%Y%m%d%H%M%S%f"),randrange(10000,99999))