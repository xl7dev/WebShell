#try:
 #   from _io import StringIO
#except:
    #from io import StringIO
import socket
from logger import debug

class UDPOut():
	def __init__(self, address, port):
		#super(UDPOut, self).__init__("", "\n")
		self.address = address
		self.port = port
		self.socket = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
		
	def write(self, s):
		try:
			self.socket.sendto(s.encode("utf-8"), (self.address, self.port))
			#debug("udpwrite successfull")
		except Exception, e:
			debug("udpwrite failed")
			debug(s)
			debug(str(e))
		
			
	def close(self):
		self.socket.close()
		