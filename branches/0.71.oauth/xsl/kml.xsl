<?xml version='1.0' encoding='utf-8'?>
<!--
XSL to convert Google Earth KML export to PorPOIse XML
Adam Moore - moore.adam@gmail.com
Centre for Geospatial Science
University of Nottingham
December 2009
Version 0.2
-->

<xsl:stylesheet version='1.0' xmlns:kml="http://www.opengis.net/kml/2.2" xmlns:xsl='http://www.w3.org/1999/XSL/Transform'>

<xsl:output method='xml' version='1.0' encoding='utf-8' indent='yes'/>

<!-- Change to whatever attribution you wish to use -->
<xsl:variable name="attribution">UoN CGS</xsl:variable>

<xsl:template match="kml:kml">
<xsl:comment>Produced using kml2layar by Adam Moore, CGS, University of Nottingham, moore.adam@gmail.com</xsl:comment>
  <pois>
  <!-- Placemark is the only KML attribute processed so far -->
  <xsl:for-each select="kml:Document/kml:Folder/kml:Placemark">
  <poi>
   <!-- ID is the position in the Placemark List -->
   <id><xsl:number value ="position()"/></id>
   <!-- Title is the KML Name -->
   <title><xsl:value-of select="kml:name" /></title>
   <!-- Lat and Lon are the first 2 strings of Point/coordinates -->
   <lat><xsl:value-of select="substring-before(substring-after(kml:Point/kml:coordinates,','),',')" /></lat>
   <lon><xsl:value-of select="substring-before(kml:Point/kml:coordinates,',')" /></lon>
   <!-- Attribution set to the variable declared at the start -->
   <attribution><xsl:value-of select="$attribution" /></attribution>
   <!-- Currently every Placemark is output as type=1 -->
   <type>1</type>
  </poi>
  </xsl:for-each>
  </pois>
</xsl:template>


</xsl:stylesheet>
