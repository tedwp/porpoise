<?xml version='1.0' encoding='utf-8'?>
<!--
XSL to convert GeoRSS from Google Maps to PorPOIse XML
Johannes la Poutre
Squio.nl <info@squio.nl>
Feb 2010
Version 0.1

Based on kml2layar by Adam Moore
-->

<xsl:stylesheet version='1.0' xmlns:georss="http://www.georss.org/georss" xmlns:gml="http://www.opengis.net/gml" xmlns:xsl='http://www.w3.org/1999/XSL/Transform'>

<xsl:output method='xml' version='1.0' encoding='utf-8' indent='yes'/>

<xsl:template match="rss">
<xsl:comment>Produced using georss2layar by Johannes la Poutre, SQUIO &lt;info@squio.nl&gt;</xsl:comment>
  <pois>
  <xsl:for-each select="channel/item">
  <poi>
   <!-- ID is the position in the Placemark List -->
   <id><xsl:number value ="position()"/></id>
   <!-- Title is the item title -->
   <title><xsl:value-of select="title" /></title>
   <line2><xsl:value-of select="description" /></line2>
   <!-- Lat and Lon are the first 2 strings of Point/coordinates -->
   <lat><xsl:value-of select="substring-before(normalize-space(georss:point),' ')" /></lat>
   <lon><xsl:value-of select="substring-after(normalize-space(georss:point),' ')" /></lon>
   <!--xsl:value-of select="normalize-space(georss:point)" /-->
   <!-- Attribution set to the POI's author -->
   <attribution><xsl:value-of select="author" /></attribution>
   <!-- Currently every Placemark is output as type=1 -->
   <type>1</type>
  </poi>
  </xsl:for-each>
  </pois>
</xsl:template>


</xsl:stylesheet>
