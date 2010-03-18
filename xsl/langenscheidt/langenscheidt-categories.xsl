<?xml version='1.0' encoding='utf-8'?>
<!--
XSL to convert Google Earth KML export to PorPOIse XML
Adam Moore - moore.adam@gmail.com
Centre for Geospatial Science
University of Nottingham
December 2009
Version 0.2
-->

<xsl:stylesheet version='1.0' xmlns:xsl='http://www.w3.org/1999/XSL/Transform'>

<xsl:output method='xml' version='1.0' encoding='utf-8' indent='yes'/>


<xsl:template match="Country/RegionCity">
<xsl:comment>Produced using langenscheidt-categories.xsl</xsl:comment>
  <categories>
  <!-- Placemark is the only KML attribute processed so far -->
  <xsl:for-each select="InfoItem">
  	<xsl:sort select="CategorieCode"/>
   <category>
       <xsl:attribute name="CategoryName">
         <xsl:value-of select="@CategorieName" />
       </xsl:attribute>
       <xsl:value-of select="@CategorieCode" />
   </category>
  </xsl:for-each>
  </categories>
</xsl:template>


</xsl:stylesheet>
