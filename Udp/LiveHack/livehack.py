"""
# Copyright (C) 2007 Nathan Ramella (nar@remix.net)
#
# This library is free software; you can redistribute it and/or
# modify it under the terms of the GNU Lesser General Public
# License as published by the Free Software Foundation; either
# version 2.1 of the License, or (at your option) any later version.
#
# This library is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
# Lesser General Public License for more details.
#
# You should have received a copy of the GNU Lesser General Public
# License along with this library; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# For questions regarding this module contact
# Nathan Ramella <nar@remix.net> or visit http://www.remix.net

This script is based off the Ableton Live supplied MIDI Remote Scripts, customised
for OSC request delivery and response. This script can be run without any extra
Python libraries out of the box. 

This is the second file that is loaded, by way of being instantiated through
__init__.py

"""

import Live
#import LiveUtils
import sys

#from Logger import log
from udpio import UDPOut

from logger import debug

# Import correct paths for os / version
version = 9
version = Live.Application.get_application().get_major_version()
if sys.platform == "win32":
	import socket   

else:
	if version > 7:
		# 10.5
		try:
			file = open("/usr/lib/python2.5/string.pyc")
		except IOError:
			sys.path.append("/Library/Frameworks/Python.framework/Versions/2.5/lib/python2.5")
			import socket_live8 as socket  
		else:
			sys.path.append("/usr/lib/python2.5")
			import socket

class LiveHack:
	__module__ = __name__
	__doc__ = "Main class that establishes the LiveHack Component"


	def __init__(self, c_instance):
		self.backout = sys.stdout
		self.backerr = sys.stderr
		self.udpout = UDPOut("localhost", 9001)
		sys.stdout = self.udpout
		sys.stderr = self.udpout
		self._LiveHack__c_instance = c_instance
		self.socket = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
		self.socket.setblocking(0)
		self.localAddr = ("localhost", 9000)
		self.socket.bind(self.localAddr)
		self.buffer = [""]
		self.bufferComplete = [False]
		self.bufferPos = 0
		self.fastUpdate = False    
        
        debug("LiveHack initialized")
        
        
	def build_midi_map(self, igwas):
		pass
		
	def processIncomingUDP(self):
		#debug("processIncomingUDP")
		execute = False
		try:
			while 1:
				self.data, self.addr = self.socket.recvfrom(65536)
				decoded = self.data.decode()
				#self.log_message("data incoming!")
				#debug("Data incoming!")
				if decoded[:1] == "!":
					#debug("Escape char!")
					if "execute" in decoded:
						#print("execute!")
						execute = True
						self.buffer += [""]
						self.bufferComplete += [False]
						self.bufferComplete[self.bufferPos] = True
						self.bufferPos += 1
					elif "clear" in decoded:
						self.clear()
					elif "knock" in decoded:
						print("\\0/ - Server says:\t\"Hi mate! Sure, the door is open! You're welcome! ;)\"")
						self.clear()
				else:
					self.buffer[self.bufferPos] += decoded

		except:
			pass
			
		if execute:
			self.execute()
			
	def clear(self):
		self.buffer = [""]
		self.bufferComplete = [False]
		self.bufferPos = 0
			
	def testInput(self, data):
		execute = False
		if len(data) == 7 and "execute" in data:
			execute = True
			self.buffer += [""]
			self.bufferComplete += [False]
			self.bufferComplete[self.bufferPos] = True
			self.bufferPos += 1
		else:
			self.buffer[self.bufferPos] += data
		if execute:
			self.execute()
			
	def execute(self):
		i = 0
		for cmd in self.buffer:
			if self.bufferComplete[i]:
				del self.buffer[i]
				del self.bufferComplete[i]
				self.bufferPos -= 1
				exec(cmd, globals(), globals())
			i += 1

	def shutdown(self):
		sys.stdout = self.backout
		sys.stderr = self.backerr
		self.udpout.close()
		self.socket.close()
######################################################################
# Standard Ableton Methods

	def connect_script_instances(self, instanciated_scripts):
		return

	def is_extension(self):
		return False

	def request_rebuild_midi_map(self):
		return
    
	def update_display(self):
		if not self.fastUpdate:
			try:
				doc = self.song()
			except:
				#log('could not get song handle')
				return
			try:
				self.time = 0
				doc.add_current_song_time_listener(self.current_song_time_changed)
				self.fastUpdate = True
			except:
				return
		self.processIncomingUDP()
        
	def current_song_time_changed(self):
		time = self.song().current_song_time
		if int(time) != self.time:
			self.time = int(time)
		self.processIncomingUDP()

	def send_midi(self, midi_event_bytes):
		pass

	def receive_midi(self, midi_bytes):
		return

	def can_lock_to_devices(self):
		return False

	def suggest_input_port(self):
		return ''

	def suggest_output_port(self):
		return ''

	def __handle_display_switch_ids(self, switch_id, value):
		pass
    
    
######################################################################
# Useful Methods

	def application(self):
		return Live.Application.get_application()

	def song(self):
		"""returns a reference to the Live Song that we do interact with"""
		return self._LiveHack__c_instance.song()

	def handle(self):
		"""returns a handle to the c_interface that is needed when forwarding MIDI events via the MIDI map"""
		return self._LiveHack__c_instance.handle()
            
	def getslots(self):
		tracks = self.song().visible_tracks

		clipSlots = []
		for track in tracks:
			clipSlots.append(track.clip_slots)
		return clipSlots
     

	def tuple_idx(self, tuple, obj):
		for i in xrange(0,len(tuple)):
			if (tuple[i] == obj):
				return i 
