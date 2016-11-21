import sys
from PyQt5.QtWidgets import QPlainTextEdit
from PyQt5.QtCore import pyqtSignal, pyqtSlot, Qt, QByteArray
from PyQt5.QtGui import QPalette, QFontMetrics

class Console(QPlainTextEdit):
	newLine = pyqtSignal(str)
	indentationPlus = pyqtSignal()
	indentationMinus = pyqtSignal()
	#suppressedKeys = [Qt.Key_Backspace, Qt.Key_Left, Qt.Key_Right, Qt.Key_Up,
		#			Qt.Key_Down]
	suppressedKeys = []
	specialKeys = [Qt.Key_Backspace, Qt.Key_Return, Qt.Key_Left, Qt.Key_Up, Qt.Key_Down, 
				Qt.Key_Tab, Qt.Key_Space]
					
	def __init__(self, parent = None):
		super(Console, self).__init__(parent)
		pal = self.palette()
		pal.setColor(QPalette.Base, Qt.black)
		pal.setColor(QPalette.Text, Qt.green)
		self.setPalette(pal)
		doc = self.document()
		font = doc.defaultFont()
		font.setFamily("Courier New")
		self.fm = QFontMetrics(font)
		#print(self.fm.width("a"))
		self.charWidth = self.fm.width("a")
		font.setPixelSize(12)
		doc.setDefaultFont(font)
		self.cmdHistory = []
		self.cmdHistoryPos = 0
		self.spaceCount = 0
		self.backSpaceCount = 0
		
	def clear(self):
		self.document().clear()
		self.makePrompt()
		
	def putData(self, data):
		self.insertPlainText(str(data))
		bar = self.verticalScrollBar()
		bar.setValue(bar.maximum())
		
	def makePrompt(self):
		self.putData(">>> ")
		
	def makeExtendedPrompt(self, indent):
		self.putData("... ")
		for i in range(indent * 4):
			self.putData(" ")
		
	def isPromptLine(self, line): 
		return line[:3] == ">>>" or line[:3] == "..."
		
	def currentLine(self):
		return self.textCursor().block().text()
		
	def keyPressEvent(self, e):
		key = e.key()
		backSpace = False
		space = False
		if key in self.suppressedKeys:
			return
		if key in self.specialKeys:
			if key == Qt.Key_Return:
				self.newLine.emit(self.currentLine())
			elif key == Qt.Key_Backspace:
				cline = self.currentLine()
				rect = self.cursorRect()
				cPos = int(rect.x() / self.charWidth)
				if cline[-1:] == " ":
					self.backSpaceCount += 1
					backSpace = True
					if self.backSpaceCount >= 4:
						self.backSpaceCount = 0
						self.indentationMinus.emit()
				#Execute it if its in command line
				if self.isPromptLine(cline) and cPos > 4:
					super(Console, self).keyPressEvent(e)
					
			elif key == Qt.Key_Tab:
				self.indentationPlus.emit()
				self.putData("    ")
			elif key == Qt.Key_Left:
				cline = self.currentLine()
				rect = self.cursorRect()
				cPos = int(rect.x() / self.charWidth)
				if self.isPromptLine(cline) and cPos > 4: 
					super(Console, self).keyPressEvent(e)
			elif key == Qt.Key_Up:
				if len(self.cmdHistory) == 0:
					return
				cmd = self.cmdHistory[self.cmdHistoryPos]
				self.lastHistoryCmdLen = len(cmd)
				self.putData(self.cmdHistory[self.cmdHistoryPos])
				self.cmdHistoryPos -= 1
				# if self.cmdHistoryPos < 0:
				self.cmdHistoryPos = 0
			elif key == Qt.Key_Space:
				space = True
				super(Console, self).keyPressEvent(e)
				self.spaceCount += 1
				if self.spaceCount >= 4:
					self.spaceCount = 0
					self.indentationPlus.emit()
			
		else:
			super(Console, self).keyPressEvent(e)
		if not backSpace:
			self.backSpaceCount = 0
		if not space:
			self.spaceCount = 0
			
	def addToCmdHistory(self, cmd):
		self.cmdHistory += [cmd]