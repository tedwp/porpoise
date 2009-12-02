<?xml version='1.0' encoding='utf-8'?>
<xsl:stylesheet version='1.0' xmlns:kml="http://www.opengis.net/kml/2.2" xmlns:xsl='http://www.w3.org/1999/XSL/Transform'>

<xsl:output method='xml' version='1.0' encoding='utf-8' indent='yes'/>

<xsl:template match="kml:kml">
  <pois>
   <xsl:value-of select="kml:Document/kml:name"/>
  <xsl:for-each select="kml:Document/kml:Folder/kml:Placemark">
  <poi>
   <id><xsl:number value ="position()"/></id>
   <title><xsl:value-of select="kml:name" /></title>
   <lat><xsl:value-of select="substring-before(substring-after(kml:Point/kml:coordinates,','),',')" /></lat>
   <lon><xsl:value-of select="substring-before(kml:Point/kml:coordinates,',')" /></lon>
   <attribution>UoN CGS</attribution>
   <type>1</type>
  </poi>
  </xsl:for-each>
  </pois>
</xsl:template>


</xsl:stylesheet>