from zope.interface import implements
from scrivener._thrift.scribe import ttypes
from scrivener._thrift.scribe import scribe

class ScribeLogHander():
    implements(scribe.Iface)

    def __init__(self):
        print("log hander inited")

    def Log(self, logEntries):
        try:
            for logEntry in logEntries:
                print(logEntry.category, logEntry.message)

            return ttypes.ResultCode.OK
        except Exception, e:
            print(e, "Error handling log entry")
            return ttypes.ResultCode.TRY_LATER