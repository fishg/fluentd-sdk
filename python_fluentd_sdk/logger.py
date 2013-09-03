#!/usr/bin/env python
# encoding: utf-8
#
# This script recive log data from Scribe-client and insert to mysql-server.
# 
from fluent import sender
from fluent import event
sender.setup('fluentd', host='192.168.5.103', port=24224)
event.Event('pythonsdk.test', {
  'from': 'userA',
  'to':   'userB'
})