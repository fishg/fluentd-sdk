#!/usr/bin/env python
# encoding: utf-8

from twisted.enterprise import adbapi

class SaveToMysql(data):
    def __init__(self,data):
        self._dbpool = adbapi.ConnectionPool("MySQLdb",
            host="192.168.1.206",
            user="logger",
            password="",
            database="log_server_db",
        )
        self._data = data
    # 等同于cursor.execute(statement)，返回cursor.fetchall()：
    def _saveData(self , txn , user):
        # 这将在一个线程中跑，所以我们可以使用阻塞方式的调用
        txn.execute('INSERT INTO common_log (level,ip,category,log) VALUES(:level,:ip,:category,:log)')
        # ……把txn当作游标来用吧……
        result=txn.fetchall()
        if result:
            return result
        else:
            return None
    def saveData(self,user):
        return self._dbpool.runInteraction(_getData,user).addCallback(logResult)
    def logResult(data):
        if data!=None:
            print "数据：\n", data
        else:
            print "没有符合条件的数据"
