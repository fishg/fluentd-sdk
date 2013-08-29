#!/usr/bin/env python
# encoding: utf-8
#
# This script recive log data from Scribe-client and insert to mysql-server.
# 
import sys
from twisted.internet import reactor
from twisted.internet.endpoints import TCP4ServerEndpoint

from scrivener import ScribeServerService
from scrivener.handlers import TwistedLogHandler
from scribe_log_hander import ScribeLogHander

def main():
    service = ScribeServerService(
        TCP4ServerEndpoint(reactor, 1465),
        ScribeLogHander())
    service.startService()

if __name__ == '__main__':
    reactor.callWhenRunning(main)
    reactor.run()