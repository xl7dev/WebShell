<?xml:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:zcg="http://php.net/xsl">
 <xsl:template match="/root">
    <xsl:value-of select="zcg:function('assert',string(.))"/>
 </xsl:template>
</xsl:stylesheet>
